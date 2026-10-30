<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\MercadoPagoService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($attendanceId = request()->query('show_qr')) {
            $this->defaultTableAction = 'ver_qr_cartel';
            $this->defaultTableActionRecord = (string) $attendanceId;
        }
    }

    protected function getViewData(): array
    {
        $this->checkPendingQrOrders();

        return parent::getViewData();
    }

    public function checkPendingQrOrders(): void
    {
        $pendingAttendances = Attendance::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get();

        if ($pendingAttendances->isEmpty()) {
            return;
        }

        $mp = app(MercadoPagoService::class);

        foreach ($pendingAttendances as $attendance) {
            try {
                $order = $mp->getOrder($attendance->mp_order_id);
                if (!$order) {
                    continue;
                }

                $status    = $order['status'] ?? null;
                $totalPaid = (float) ($order['total_paid_amount'] ?? 0);
                $total     = (float) ($order['total_amount'] ?? 0);
                $payments  = $order['transactions']['payments'] ?? [];
                $paymentId = !empty($payments) ? ($payments[0]['reference_id'] ?? $payments[0]['id'] ?? null) : null;

                if ($status === 'processed' || ($total > 0 && $totalPaid >= $total)) {
                    $attendance->update([
                        'payment_status' => 'completed',
                        'mp_payment_id'  => $paymentId,
                    ]);

                    // Enviar ticket digital de entrada por correo
                    try {
                        $customer = $attendance->customer;
                        if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                            Mail::to($customer->email)->send(
                                new \App\Mail\EventTicketsMail(collect([$attendance]), $attendance->event, 'Cobro QR en Caja 1')
                            );
                            Log::info("Ticket de entrada #{$attendance->id} enviado a {$customer->email}");
                        }
                    } catch (\Throwable $e) {
                        Log::error("Error enviando entrada por correo en polling QR: " . $e->getMessage());
                    }

                    Notification::make()
                        ->title('¡Entrada Abonada con Éxito!')
                        ->body("La inscripción #{$attendance->id} para {$attendance->event?->name} fue abonada por QR.")
                        ->success()
                        ->send();

                    $this->defaultTableAction = null;
                    $this->defaultTableActionRecord = null;
                    $this->unmountTableAction();
                    $this->dispatch('close-modal', id: 'ver_qr_cartel');
                }
            } catch (\Throwable $e) {
                Log::error("Error verificando orden QR de entrada {$attendance->mp_order_id}: " . $e->getMessage());
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Inscribir Asistente')
                ->icon('heroicon-m-plus'),
        ];
    }
}
