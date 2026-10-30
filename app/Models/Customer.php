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
        'user_id',
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

    public bool $create_web_account = false;

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            // Solo crea cuenta de usuario web si se solicita explícitamente o si se crea desde el ERP administrativo
            $isErp = request()->is('admin*') || (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::isServing());
            $shouldCreateAccount = ($customer->create_web_account ?? false) || $isErp;

            if ($shouldCreateAccount && ! $customer->user_id && filled($customer->email)) {
                $plainPassword = $customer->document_number ?: '12345678';
                $user = User::firstOrCreate(
                    ['email' => $customer->email],
                    [
                        'name'     => trim($customer->first_name . ' ' . $customer->last_name),
                        'password' => bcrypt($plainPassword),
                    ]
                );

                $clienteRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
                if (! $user->hasRole('Cliente')) {
                    $user->assignRole($clienteRole);
                }

                $customer->user_id = $user->id;

                if ($user->wasRecentlyCreated) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($user->email)->send(
                            new \App\Mail\WelcomeUserMail($user, $plainPassword, 'presencial')
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Error enviando correo de bienvenida a {$user->email}: " . $e->getMessage());
                    }
                }
            }
        });

        static::updating(function (Customer $customer) {
            $isErp = request()->is('admin*') || (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::isServing());
            $shouldCreateAccount = ($customer->create_web_account ?? false) || $isErp;

            if ($shouldCreateAccount && ! $customer->user_id && filled($customer->email)) {
                $plainPassword = $customer->document_number ?: '12345678';
                $user = User::firstOrCreate(
                    ['email' => $customer->email],
                    [
                        'name'     => trim($customer->first_name . ' ' . $customer->last_name),
                        'password' => bcrypt($plainPassword),
                    ]
                );

                $clienteRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
                if (! $user->hasRole('Cliente')) {
                    $user->assignRole($clienteRole);
                }

                $customer->user_id = $user->id;

                if ($user->wasRecentlyCreated) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($user->email)->send(
                            new \App\Mail\WelcomeUserMail($user, $plainPassword, 'presencial')
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Error enviando correo de bienvenida a {$user->email}: " . $e->getMessage());
                    }
                }
            } elseif ($customer->user) {
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