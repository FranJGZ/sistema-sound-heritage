<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {
    protected $fillable = ['purchase_date', 'total', 'supplier_id'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchaseDetails() { return $this->hasMany(PurchaseDetail::class); }
}