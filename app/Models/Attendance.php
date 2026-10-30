<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Attendance extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'registration_date',
        'customer_id',
        'event_id',
        'price',
        'quantity',
        'payment_method',
        'payment_status',
        'mp_order_id',
        'mp_payment_id',
        'mp_refund_id',
        'refunded_at',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'price'             => 'decimal:2',
        'quantity'          => 'integer',
        'refunded_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Attendance $attendance) {
            if ($attendance->payment_status === 'completed' && (!empty($attendance->mp_payment_id) || !empty($attendance->mp_order_id))) {
                try {
                    $mp = app(\App\Services\MercadoPagoService::class);
                    $mp->refundAttendance($attendance);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Error reembolsando entrada de evento #{$attendance->id}: " . $e->getMessage());
                }
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }
}