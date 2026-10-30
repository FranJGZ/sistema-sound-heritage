<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EventSponsor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['event_id', 'sponsor_id'];
    public function event() { return $this->belongsTo(Event::class); }
    public function sponsor() { return $this->belongsTo(Sponsor::class); }
}