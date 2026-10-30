<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Invoice extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'invoice_type',
        'point_of_sale',
        'receipt_number',
        'issue_date',
        'due_date',
        'total',
        'store_id',
        'customer_id',
        'employee_id',
        'mp_order_id',
        'mp_payment_id',
        'mp_refund_id',
        'refunded_at',
        'payment_status',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'total'       => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Invoice $invoice) {
            if (! $invoice->isForceDeleting()) {
                // 1. Reintegrar stock al inventario a través de la instancia del modelo para disparar eventos de auditoría
                foreach ($invoice->invoiceDetails as $detail) {
                    if ($detail->product_id) {
                        $product = Product::find($detail->product_id);
                        if ($product) {
                            $product->stock += $detail->quantity;
                            $product->save();
                        }
                    }
                }

                // 2. Si fue cobrada por Mercado Pago (Checkout Pro o QR), reintegrar el dinero automáticamente
                if ($invoice->payment_status === 'completed' && (!empty($invoice->mp_payment_id) || !empty($invoice->mp_order_id))) {
                    try {
                        $mp = app(\App\Services\MercadoPagoService::class);
                        $mp->refundInvoice($invoice);
                        $invoice->payment_status = 'refunded';
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Error procesando reembolso automático de factura #{$invoice->id}: " . $e->getMessage());
                    }
                }
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public static function getNextReceiptNumber(string $pointOfSale = '0001'): string
    {
        $maxNum = static::withTrashed()
            ->selectRaw('MAX(CAST(receipt_number AS UNSIGNED)) as max_num')
            ->value('max_num');

        $next = ($maxNum ? (int)$maxNum : static::withTrashed()->count()) + 1;
        return str_pad((string)$next, 8, '0', STR_PAD_LEFT);
    }
}