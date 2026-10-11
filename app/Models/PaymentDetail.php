<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentDetail extends Model {
    protected $fillable = ['invoice_id', 'payment_method_id'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
}