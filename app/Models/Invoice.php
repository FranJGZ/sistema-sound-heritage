<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model {
    protected $fillable = ['invoice_type', 'point_of_sale', 'receipt_number', 'issue_date', 'due_date', 'total', 'store_id', 'customer_id', 'employee_id'];
    public function store() { return $this->belongsTo(Store::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function invoiceDetails() { return $this->hasMany(InvoiceDetail::class); }
    public function paymentDetails() { return $this->hasMany(PaymentDetail::class); }
}