<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ['type', 'name', 'price', 'stock'];
    public function purchaseDetails() { return $this->hasMany(PurchaseDetail::class); }
    public function invoiceDetails() { return $this->hasMany(InvoiceDetail::class); }
}