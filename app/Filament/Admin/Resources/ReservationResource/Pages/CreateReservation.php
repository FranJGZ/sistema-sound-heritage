<?php

namespace App\Filament\Admin\Resources\ReservationResource\Pages;

use App\Filament\Admin\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\MercadoPagoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $selectedPaymentMode = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $venueId = $data['venue_id'] ?? 1;
        $date = $data['date'] ?? null;
        $shift = $data['shift'] ?? null;

        // Validación estricta en servidor contra choques con eventos y reservas en el mismo turno
        if ($date) {
            $eventExists = \App\Models\Event::where('venue_id', $venueId)
                ->whereDate('date', $date)
                ->exists();

            if ($eventExists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.date' => 'El salón ya se encuentra ocupado por un Evento cultural de Sound Heritage en esa fecha.',
                ]);
            }

            if ($shift) {
                $reservationExists = Reservation::where('venue_id', $venueId)
                    ->whereDate('date', $date)
                    ->where('shift', $shift)
                    ->where('status', '!=', 'Cancelada')
                    ->exists();

                if ($reservationExists) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'data.shift' => "El salón ya cuenta con una reserva activa o pendiente para esa fecha durante el turno {$shift}.",
                    ]);
                }
            }
        }

        $isMp = ($data['payment_method'] ?? null) === 'Mercado Pago';
        $paymentMode = $data['payment_mode'] ?? 'seña';
        $this->selectedPaymentMode = $paymentMode;
        $price = (float) ($data['price'] ?? 0);

        // Una reserva nueva NUNCA puede nacer 'Confirmada' directamente sin haber acreditado pago (Opción A)
        $data['status'] = 'Pendiente';

        // Si es Mercado Pago, queda esperando el escaneo del QR en Caja 1
        if ($isMp) {
            $data['payment_status'] = 'pending';
            $data['deposit_amount'] = ($paymentMode === 'total') ? $price : round($price * 0.30, 2);
        } else {
            // Efectivo en Mostrador / Transferencia presencial
            if ($paymentMode === 'total') {
                $data['payment_status'] = 'Totalmente Abonada';
                $data['deposit_amount'] = $price;
            } else {
                $data['payment_status'] = 'Señada (Resta 70%)';
                $data['deposit_amount'] = round($price * 0.30, 2);
            }
        }

        // Remueve payment_mode para evitar error en el insert Eloquent si la columna no existe en la BD
        unset($data['payment_mode']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // 1. Notificar al cliente por correo electrónico con el detalle de la solicitud de reserva
        try {
            $customer = $this->record->customer;
            if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                    new \App\Mail\ReservationPendingMail($this->record)
                );
                Log::info("Mail de reserva registrada en ERP enviado a {$customer->email} para Reserva #{$this->record->id}");
            }
        } catch (\Throwable $e) {
            Log::error("Error enviando mail de reserva desde ERP: " . $e->getMessage());
        }

        // 2. Si el método elegido es Mercado Pago, enviar orden QR inmediatamente a Caja 1
        if ($this->record->payment_method === 'Mercado Pago') {
            $paymentMode = $this->selectedPaymentMode ?? ($this->data['payment_mode'] ?? 'seña');
            $monto = ($paymentMode === 'total')
                ? (float) $this->record->price
                : (float) $this->record->deposit_amount;

            if ($monto <= 0) {
                $monto = round((float) $this->record->price * 0.30, 2);
            }

            try {
                $mp = app(MercadoPagoService::class);
                // Liberar cualquier orden previa que haya quedado en la Caja 1
                $mp->cancelAnyActiveCajaOrder('CAJA1');

                $order = $mp->createQrOrder(
                    amount: $monto,
                    description: "Reserva #{$this->record->id} - {$this->record->type} (" . ($paymentMode === 'total' ? 'Pago Total' : 'Seña 30%') . ")",
                    externalReference: ($paymentMode === 'total') ? "RES-{$this->record->id}-TOTAL" : "RES-{$this->record->id}-SENA",
                    posId: 'CAJA1'
                );

                if ($order && isset($order['id'])) {
                    $this->record->update([
                        'mp_order_id' => $order['id'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Error enviando orden QR automática de reserva a Caja 1: " . $e->getMessage());
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        // Si es Mercado Pago, redirigir abriendo el modal del QR en pantalla usando el mecanismo nativo
        if ($this->record->payment_method === 'Mercado Pago' || !empty($this->record->mp_order_id)) {
            return ReservationResource::getUrl('index', [
                'tableAction'       => 'ver_qr_cartel',
                'tableActionRecord' => (string) $this->record->id,
            ]);
        }

        return ReservationResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->record->payment_method === 'Mercado Pago') {
            return Notification::make()
                ->title('Orden QR enviada a Caja 1')
                ->body('El cliente debe escanear el cartel QR para abonar la seña o total de la reserva.')
                ->warning();
        }

        if ($this->record->payment_status === 'Totalmente Abonada') {
            return Notification::make()
                ->title('Reserva registrada y cobrada')
                ->body('Se cobró el total del alquiler en mostrador. La reserva quedó Totalmente Abonada.')
                ->success();
        }

        if ($this->record->payment_status === 'Señada (Resta 70%)') {
            return Notification::make()
                ->title('Reserva registrada con seña')
                ->body('Se cobró la seña del 30% en mostrador. El 70% restante queda pendiente para el día del turno.')
                ->success();
        }

        return Notification::make()
            ->title('Reserva registrada')
            ->body('La reserva fue guardada correctamente.')
            ->success();
    }
}
