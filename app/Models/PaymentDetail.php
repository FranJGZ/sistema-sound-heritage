<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['invoice_id', 'payment_method_id'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
}