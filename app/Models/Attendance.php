<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Attendance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'registration_date',
        'customer_id',
        'event_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }
}