<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {

    use SoftDeletes;
    protected $fillable = [
        'type', 
        'name', 
        'price', 
        'stock',
        'specs'];
    protected $casts = [
        'specs' => 'array', 
    ];
    public function purchaseDetails() { return $this->hasMany(PurchaseDetail::class); }
    
    public function invoiceDetails() { return $this->hasMany(InvoiceDetail::class); }
}