<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Customer extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_number',
        'last_name',
        'first_name',
        'address',
        'phone',
        'tax_id',
        'tax_condition',
        'email',
        'city_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (! $customer->user_id && filled($customer->email)) {
                $user = User::firstOrCreate(
                    ['email' => $customer->email],
                    [
                        'name'     => trim($customer->first_name . ' ' . $customer->last_name),
                        'password' => bcrypt($customer->document_number),
                    ]
                );

                if (! $user->hasRole('Cliente')) {
                    $user->assignRole('Cliente');
                }

                $customer->user_id = $user->id;
            }
        });

        static::updating(function (Customer $customer) {
            if ($customer->user) {
                $customer->user->update([
                    'name'  => trim($customer->first_name . ' ' . $customer->last_name),
                    'email' => $customer->email,
                ]);
            }
        });

        static::deleting(function (Customer $customer) {
            if (! $customer->isForceDeleting() && $customer->user) {
                $customer->user->delete();
            }
        });

        static::restoring(function (Customer $customer) {
            if ($customer->user && $customer->user->trashed()) {
                $customer->user->restore();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}