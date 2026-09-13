<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sponsor extends Model {
    protected $fillable = ['name', 'type', 'phone', 'email'];
    public function eventSponsors() { return $this->hasMany(EventSponsor::class); }
}