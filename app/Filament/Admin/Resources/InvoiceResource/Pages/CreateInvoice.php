<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\PaymentMethod;
use App\Services\MercadoPagoService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['employee_id'] = InvoiceResource::getCurrentEmployee()?->id ?? $data['employee_id'] ?? null;

        // Si el medio de pago seleccionado es Mercado Pago, el cobro queda automáticamente como pendiente
        $isMp = false;
        $mpId = PaymentMethod::where('name', 'Mercado Pago')->value('id');
        $paymentDetails = $this->data['paymentDetails'] ?? $data['paymentDetails'] ?? [];
        if (!empty($paymentDetails)) {
            foreach ($paymentDetails as $detail) {
                if (($detail['payment_method_id'] ?? null) == $mpId) {
                    $isMp = true;
                    break;
                }
            }
        }

        $data['payment_status'] = $isMp ? 'pending' : 'completed';

        return $data;
    }

    protected function afterCreate(): void
    {
        $mpId = PaymentMethod::where('name', 'Mercado Pago')->value('id');
        $isMp = false;

        $paymentDetails = $this->data['paymentDetails'] ?? [];
        foreach ($paymentDetails as $detail) {
            if (($detail['payment_method_id'] ?? null) == $mpId) {
                $isMp = true;
                break;
            }
        }

        if (!$isMp) {
            $isMp = $this->record->paymentDetails()->where('payment_method_id', $mpId)->exists();
        }

        // Si el cobro es por Mercado Pago, enviar la orden inmediatamente al QR de Caja 1
        if ($isMp) {
            $this->record->update(['payment_status' => 'pending']);

            try {
                $mp = app(MercadoPagoService::class);
                // Liberar cualquier orden previa que haya quedado pendiente en la Caja 1
                $mp->cancelAnyActiveCajaOrder('CAJA1');

                $order = $mp->createQrOrder(
                    amount: (float) $this->record->total,
                    description: "Factura FC {$this->record->invoice_type} {$this->record->point_of_sale}-{$this->record->receipt_number}",
                    externalReference: "INV-{$this->record->id}",
                    posId: 'CAJA1'
                );

                if ($order && isset($order['id'])) {
                    $this->record->update([
                        'mp_order_id' => $order['id'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Error enviando orden automática de QR a Caja 1: " . $e->getMessage());
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        // Si es Mercado Pago, redirigir abriendo automáticamente el modal del QR en pantalla
        if ($this->record->payment_status === 'pending' || !empty($this->record->mp_order_id)) {
            return InvoiceResource::getUrl('index', [
                'tableAction' => 'ver_qr_cartel',
                'tableActionRecord' => (string) $this->record->id,
            ]);
        }

        return InvoiceResource::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->record->payment_status === 'pending') {
            return Notification::make()
                ->title('Orden QR enviada a Caja 1')
                ->body('La orden está activa. El cliente debe escanear el cartel QR para abonar. Estado: Esperando QR.')
                ->warning();
        }

        return Notification::make()
            ->title('Venta registrada')
            ->body('La factura fue cobrada en efectivo y emitida correctamente.')
            ->success();
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Emitir Factura / Cobrar')
            ->icon('heroicon-m-check-circle');
    }
}