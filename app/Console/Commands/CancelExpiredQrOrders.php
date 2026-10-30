<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredQrOrders extends Command
{
    protected $signature = 'invoices:cancel-expired-qr';
    protected $description = 'Anula automáticamente las órdenes QR de Mercado Pago pendientes con más de 24 horas y restituye el stock.';

    public function handle(): int
    {
        $expiredInvoices = Invoice::where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->where(function ($q) {
                $q->whereNotNull('mp_order_id')
                  ->orWhereHas('paymentDetails.paymentMethod', function ($pmQ) {
                      $pmQ->where('name', 'like', '%Mercado Pago%');
                  });
            })
            ->get();

        $count = $expiredInvoices->count();
        $this->info("Encontradas {$count} facturas QR pendientes con más de 24 horas.");

        $mpService = app(MercadoPagoService::class);

        foreach ($expiredInvoices as $invoice) {
            try {
                // Cancelar orden activa en MP si corresponde
                $mpService->cancelAnyActiveCajaOrder();

                $invoice->payment_status = 'failed';
                $invoice->saveQuietly();

                // El delete() soft-delete restituye automáticamente el stock y registra auditorías
                $invoice->delete();

                Log::info("Factura QR #{$invoice->id} anulada automáticamente por expiración de 24 horas.");
            } catch (\Throwable $e) {
                Log::error("Error al anular factura QR expirada #{$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("Proceso completado. Se anularon {$count} facturas expiradas.");
        return Command::SUCCESS;
    }
}

