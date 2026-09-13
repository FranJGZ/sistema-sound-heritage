<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventSponsor extends Model {
    protected $fillable = ['event_id', 'sponsor_id'];
    public function event() { return $this->belongsTo(Event::class); }
    public function sponsor() { return $this->belongsTo(Sponsor::class); }
}