<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $fillable = ['quantity', 'price', 'purchase_id', 'product_id'];

    protected static function booted(): void
    {
        static::created(function (PurchaseDetail $detail) {
            Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);
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