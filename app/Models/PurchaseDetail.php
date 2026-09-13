<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseDetail extends Model {
    protected $fillable = ['quantity', 'price', 'purchase_id', 'product_id'];
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function product() { return $this->belongsTo(Product::class); }
}