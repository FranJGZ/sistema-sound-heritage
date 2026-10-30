<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InvoiceDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['quantity', 'unit_price', 'subtotal', 'invoice_id', 'product_id', 'description'];

    protected static function booted(): void
    {
        static::created(function (InvoiceDetail $detail) {
            if ($detail->product_id) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock = max(0, $product->stock - $detail->quantity);
                    $product->save();
                }
            }
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