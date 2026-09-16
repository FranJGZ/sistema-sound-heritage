<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $fillable = ['purchase_date', 'total', 'supplier_id'];

    protected static function booted(): void
    {
        static::deleting(function (Purchase $purchase) {
            if (! $purchase->isForceDeleting()) {
                foreach ($purchase->purchaseDetails as $detail) {
                    Product::where('id', $detail->product_id)
                        ->decrement('stock', $detail->quantity);
                }
            }
        });

        static::restoring(function (Purchase $purchase) {
            foreach ($purchase->purchaseDetails as $detail) {
                Product::where('id', $detail->product_id)
                    ->increment('stock', $detail->quantity);
            }
        });
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}