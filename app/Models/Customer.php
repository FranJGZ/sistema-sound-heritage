<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
    protected $fillable = ['document_number', 'last_name', 'first_name', 'address', 'phone', 'tax_id', 'tax_condition', 'email', 'city_id'];
    public function city() { return $this->belongsTo(City::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
}