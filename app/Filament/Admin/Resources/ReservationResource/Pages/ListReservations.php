<?php

namespace App\Filament\Admin\Resources\ReservationResource\Pages;

use App\Filament\Admin\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\MercadoPagoService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    public function mount(): void
    {
        parent::mount();

        // Soporte nativo para abrir el modal del QR si viene el parámetro show_qr
        if ($reservationId = request()->query('show_qr')) {
            $this->defaultTableAction = 'ver_qr_cartel';
            $this->defaultTableActionRecord = (string) $reservationId;
        }
    }

    protected function getViewData(): array
    {
        $this->checkPendingQrOrders();

        return parent::getViewData();
    }

    /**
     * Polling en tiempo real para verificar si las reservas pendientes de cobro QR en Caja 1 ya fueron abonadas.
     */
    public function checkPendingQrOrders(): void
    {
        $pendingReservations = Reservation::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get();

        if ($pendingReservations->isEmpty()) {
            return;
        }

        $mp = app(MercadoPagoService::class);

        foreach ($pendingReservations as $reservation) {
            try {
                $order = $mp->getOrder($reservation->mp_order_id);
                if (!$order) {
                    continue;
                }

                $status    = $order['status'] ?? null;
                $totalPaid = (float) ($order['total_paid_amount'] ?? 0);
                $total     = (float) ($order['total_amount'] ?? 0);
                $payments  = $order['transactions']['payments'] ?? [];
                $paymentId = !empty($payments) ? ($payments[0]['reference_id'] ?? $payments[0]['id'] ?? null) : null;

                if ($status === 'processed' || ($total > 0 && $totalPaid >= $total)) {
                    $isTotalPaid = ($totalPaid >= (float) $reservation->price && (float) $reservation->price > 0);
                    $newPaymentStatus = $isTotalPaid
                        ? 'Totalmente Abonada'
                        : 'Señada (Resta 70%)';

                    $updateData = [
                        'payment_status' => $newPaymentStatus,
                        'mp_payment_id'  => $paymentId,
                    ];
                    if ($isTotalPaid) {
                        $updateData['deposit_amount'] = (float) $reservation->price;
                    }

                    // Opción A: Mantener el status operativo 'Pendiente' hasta que el encargado la apruebe manualmente
                    $reservation->update($updateData);

                    Notification::make()
                        ->title('¡Pago de reserva acreditado con éxito!')
                        ->body("El cobro de la reserva #{$reservation->id} fue acreditado por QR ({$newPaymentStatus}). La reserva continúa Pendiente de aprobación por disponibilidad técnica.")
                        ->success()
                        ->send();

                    // Cerrar el modal del QR y limpiar la acción por defecto
                    $this->defaultTableAction = null;
                    $this->defaultTableActionRecord = null;
                    $this->unmountTableAction();
                    $this->dispatch('close-modal', id: 'ver_qr_cartel');
                }
            } catch (\Throwable $e) {
                Log::error("Error verificando orden QR de reserva {$reservation->mp_order_id}: " . $e->getMessage());
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
