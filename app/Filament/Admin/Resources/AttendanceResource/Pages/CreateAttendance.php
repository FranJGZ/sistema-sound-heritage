<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Models\Event;
use App\Services\MercadoPagoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $event = Event::find($data['event_id'] ?? null);
        $qty = max(1, (int) ($data['quantity'] ?? 1));
        $unitPrice = $event ? (float) $event->price : 0;
        $totalPrice = $unitPrice * $qty;

        $data['quantity'] = $qty;
        $data['price'] = $totalPrice;

        $isMp = ($data['payment_method'] ?? '') === 'Mercado Pago';

        if ($totalPrice > 0 && $isMp) {
            $data['payment_status'] = 'pending';
        } else {
            $data['payment_status'] = 'completed';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // 1. Si el cobro está completado (efectivo o gratuito), despachar las entradas por correo con el PDF adjunto
        if ($this->record->payment_status === 'completed') {
            try {
                $customer = $this->record->customer;
                if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                    \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                        new \App\Mail\EventTicketsMail(
                            customer: $customer,
                            attendances: collect([$this->record])
                        )
                    );
                    Log::info("Entrada digital #{$this->record->id} enviada por correo a {$customer->email}");
                }
            } catch (\Throwable $e) {
                Log::error("Error enviando entrada por correo tras crear en ERP: " . $e->getMessage());
            }
        }

        // 2. Si el pago es por Mercado Pago, enviar la orden al cartel QR de Caja 1
        if ($this->record->payment_status === 'pending' && (float) $this->record->price > 0) {
            try {
                $mp = app(MercadoPagoService::class);
                // Liberar cualquier orden previa en Caja 1
                $mp->cancelAnyActiveCajaOrder('CAJA1');

                $qty = $this->record->quantity ?: 1;
                $order = $mp->createQrOrder(
                    amount: (float) $this->record->price,
                    description: "Entradas ({$qty}x): " . ($this->record->event?->name ?? 'Entrada'),
                    externalReference: "ATT-{$this->record->id}",
                    posId: 'CAJA1'
                );

                if ($order && isset($order['id'])) {
                    $this->record->update([
                        'mp_order_id' => $order['id'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Error enviando orden QR automática de entrada a Caja 1: " . $e->getMessage());
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->record->payment_status === 'pending' && !empty($this->record->mp_order_id)) {
            return AttendanceResource::getUrl('index', [
                'tableAction' => 'ver_qr_cartel',
                'tableActionRecord' => (string) $this->record->id,
            ]);
        }

        return AttendanceResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->record->payment_status === 'pending') {
            return Notification::make()
                ->title('Inscripción registrada — Esperando cobro por QR')
                ->body('El código QR de Caja 1 está listo en pantalla para escanear.')
                ->warning();
        }

        return Notification::make()
            ->title('Asistente inscripto con éxito.')
            ->success();
    }
}
