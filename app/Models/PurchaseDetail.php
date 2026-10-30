<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PurchaseDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['quantity', 'price', 'purchase_id', 'product_id'];

    protected static function booted(): void
    {
        static::created(function (PurchaseDetail $detail) {
            if ($detail->product_id) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->stock += $detail->quantity;
                    $product->save();
                }
            }
        });
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}