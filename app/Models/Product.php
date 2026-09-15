<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ['type', 'name', 'price', 'stock','specs'];
    public function purchaseDetails() { return $this->hasMany(PurchaseDetail::class); }
    protected $casts = [
        'specs' => 'array', 
    ];
    public function invoiceDetails() { return $this->hasMany(InvoiceDetail::class); }
}