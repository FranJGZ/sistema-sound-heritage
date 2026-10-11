<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $fillable = ['quantity', 'unit_price', 'subtotal', 'invoice_id', 'product_id'];

    protected static function booted(): void
    {
        static::created(function (InvoiceDetail $detail) {
            Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}