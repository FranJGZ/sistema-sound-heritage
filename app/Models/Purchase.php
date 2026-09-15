<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model {
    use HasFactory, SoftDeletes; // 2. Usarlo aquí adentro
    protected $guarded = [];
    protected $fillable = ['purchase_date', 'total', 'supplier_id'];
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchaseDetails() { return $this->hasMany(PurchaseDetail::class); }
}