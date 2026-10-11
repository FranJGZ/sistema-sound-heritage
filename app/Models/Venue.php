<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Venue extends Model {
    protected $fillable = ['name', 'capacity', 'location'];
    public function events() { return $this->hasMany(Event::class); }
    public function reservations() { return $this->hasMany(Reservation::class); }
}