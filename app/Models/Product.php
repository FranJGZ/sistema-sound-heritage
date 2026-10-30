<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'type', 
        'name', 
        'brand',
        'model',
        'image',
        'price', 
        'discount_percentage',
        'stock',
        'specs',
    ];

    protected $casts = [
        'specs' => 'array', 
        'discount_percentage' => 'integer',
    ];

    public function getOfferSavingsAttribute(): float
    {
        if ($this->discount_percentage && $this->discount_percentage > 0) {
            return (float) ($this->price * ($this->discount_percentage / 100));
        }
        return 0.0;
    }

    public function getOfferPriceAttribute(): float
    {
        if ($this->discount_percentage && $this->discount_percentage > 0) {
            return (float) ($this->price - $this->offer_savings);
        }
        return (float) $this->price;
    }

    public function getCashPriceAttribute(): float
    {
        return (float) round($this->offer_price * 0.90, 2);
    }

    public function getTotalCashSavingsAttribute(): float
    {
        return (float) max(0, $this->price - $this->cash_price);
    }

    public function getImagePathAttribute(): ?string
    {
        return $this->image ? 'storage/' . $this->image : null;
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
