<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Event extends Model {
    protected $fillable = ['name', 'date', 'type', 'capacity', 'venue_id'];
    public function venue() { return $this->belongsTo(Venue::class); }
    public function eventSponsors() { return $this->hasMany(EventSponsor::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
}