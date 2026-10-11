<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reservation extends Model {
    protected $fillable = ['type', 'date', 'status', 'customer_id', 'venue_id'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function venue() { return $this->belongsTo(Venue::class); }
}