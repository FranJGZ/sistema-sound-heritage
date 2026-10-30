<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Event extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'date', 'time', 'type', 'description', 'capacity', 'price', 'venue_id'];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function eventSponsors()
    {
        return $this->hasMany(EventSponsor::class);
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'event_sponsors')->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getOccupiedSpotsAttribute(): int
    {
        return (int) $this->attendances()->sum('quantity');
    }

    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->capacity - $this->occupied_spots);
    }
}