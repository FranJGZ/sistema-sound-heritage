<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Purchase extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'purchase_date',
        'total',
        'supplier_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Purchase $purchase) {
            if (! $purchase->isForceDeleting()) {
                foreach ($purchase->purchaseDetails as $detail) {
                    if ($detail->product_id) {
                        $product = Product::find($detail->product_id);
                        if ($product) {
                            $product->stock = max(0, $product->stock - $detail->quantity);
                            $product->save();
                        }
                    }
                }
            }
        });

        static::restoring(function (Purchase $purchase) {
            foreach ($purchase->purchaseDetails as $detail) {
                if ($detail->product_id) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->stock += $detail->quantity;
                        $product->save();
                    }
                }
            }
        });
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
