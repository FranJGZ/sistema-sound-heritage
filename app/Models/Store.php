<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Store extends Model {
    protected $fillable = ['company_name', 'address', 'tax_condition', 'phone', 'tax_id', 'start_date'];
    public function invoices() { return $this->hasMany(Invoice::class); }
}