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
    ];

    protected static function booted(): void
    {
        static::deleting(function (Invoice $invoice) {
            if (! $invoice->isForceDeleting()) {
                foreach ($invoice->invoiceDetails as $detail) {
                    Product::where('id', $detail->product_id)
                        ->increment('stock', $detail->quantity);
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
}