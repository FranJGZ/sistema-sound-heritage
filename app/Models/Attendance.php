<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $fillable = ['registration_date', 'customer_id', 'event_id'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function event() { return $this->belongsTo(Event::class); }
}