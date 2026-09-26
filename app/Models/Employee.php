<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_number',
        'last_name',
        'first_name',
        'address',
        'phone',
        'city_id',
        'employee_category_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (! $employee->user_id) {
                $slug = Str::slug($employee->first_name . '.' . $employee->last_name, '.');
                $email = $slug . '.' . $employee->document_number . '@soundheritage.com';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => trim($employee->first_name . ' ' . $employee->last_name),
                        'password' => bcrypt($employee->document_number),
                    ]
                );

                if (! $user->hasAnyRole(['Administrador', 'Encargado de Stock', 'Vendedor'])) {
                    $user->assignRole('Vendedor');
                }

                $employee->user_id = $user->id;
            }
        });

        static::updating(function (Employee $employee) {
            if ($employee->user) {
                $employee->user->update([
                    'name' => trim($employee->first_name . ' ' . $employee->last_name),
                ]);
            }
        });

        static::deleting(function (Employee $employee) {
            if (! $employee->isForceDeleting() && $employee->user) {
                $employee->user->delete();
            }
        });

        static::restoring(function (Employee $employee) {
            if ($employee->user && $employee->user->trashed()) {
                $employee->user->restore();
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

    public function employeeCategory()
    {
        return $this->belongsTo(EmployeeCategory::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}