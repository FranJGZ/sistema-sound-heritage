<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\MercadoPagoService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    public function mount(): void
    {
        parent::mount();

        // Limpieza automática de órdenes QR pendientes que superaron las 24 horas
        \Illuminate\Support\Facades\Artisan::call('invoices:cancel-expired-qr');

        // Soporte nativo para abrir el modal del QR si viene el parámetro show_qr
        if ($invoiceId = request()->query('show_qr')) {
            $this->defaultTableAction = 'ver_qr_cartel';
            $this->defaultTableActionRecord = (string) $invoiceId;
        }
    }

    protected function getViewData(): array
    {
        $this->checkPendingQrOrders();

        return parent::getViewData();
    }

    /**
     * Polling en tiempo real para verificar si las órdenes pendientes de QR en Caja 1 ya fueron abonadas.
     */
    public function checkPendingQrOrders(): void
    {
        $pendingInvoices = Invoice::where('payment_status', 'pending')
            ->whereNotNull('mp_order_id')
            ->get();

        if ($pendingInvoices->isEmpty()) {
            return;
        }

        $mp = app(MercadoPagoService::class);

        foreach ($pendingInvoices as $invoice) {
            try {
                $order = $mp->getOrder($invoice->mp_order_id);
                if (!$order) {
                    continue;
                }

                $status    = $order['status'] ?? null;
                $totalPaid = (float) ($order['total_paid_amount'] ?? 0);
                $total     = (float) ($order['total_amount'] ?? 0);
                $payments  = $order['transactions']['payments'] ?? [];
                $paymentId = !empty($payments) ? ($payments[0]['reference_id'] ?? $payments[0]['id'] ?? null) : null;

                if ($status === 'processed' || ($total > 0 && $totalPaid >= $total)) {
                    $invoice->update([
                        'payment_status' => 'completed',
                        'mp_payment_id'  => $paymentId,
                    ]);

                    // Enviar ticket fiscal por correo al cliente si tiene email
                    try {
                        $customer = $invoice->customer;
                        if ($customer && !empty($customer->email) && filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                            Mail::to($customer->email)->send(
                                new \App\Mail\PurchaseConfirmationMail($invoice, 'Mostrador / Cobro QR Caja 1', collect())
                            );
                            Log::info("Ticket de factura #{$invoice->id} enviado por correo a {$customer->email}");
                        }
                    } catch (\Throwable $e) {
                        Log::error("Error enviando comprobante por correo en polling QR: " . $e->getMessage());
                    }

                    Notification::make()
                        ->title('¡Pago acreditado con éxito!')
                        ->body("La factura #{$invoice->receipt_number} por $ " . number_format((float) $invoice->total, 2, ',', '.') . " fue abonada por QR.")
                        ->success()
                        ->send();

                    // Cerrar el modal del QR y limpiar la acción por defecto
                    $this->defaultTableAction = null;
                    $this->defaultTableActionRecord = null;
                    $this->unmountTableAction();
                    $this->dispatch('close-modal', id: 'ver_qr_cartel');
                }
            } catch (\Throwable $e) {
                Log::error("Error verificando estado de orden QR {$invoice->mp_order_id}: " . $e->getMessage());
            }
        }
    }

    public function getTabs(): array
    {
        return [
            'emitidas' => \Filament\Resources\Components\Tab::make('Emitidas')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->withoutTrashed())
                ->badge(Invoice::withoutTrashed()->count()),
            'anuladas' => \Filament\Resources\Components\Tab::make('Anuladas')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->onlyTrashed())
                ->badge(Invoice::onlyTrashed()->count()),
            'todas' => \Filament\Resources\Components\Tab::make('Todas')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->withTrashed()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
