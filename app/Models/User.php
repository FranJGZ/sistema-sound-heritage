<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class User extends Authenticatable implements Auditable, FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];



    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer()
    {
        return $this->hasOne(Customer::class)->withTrashed();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class)->withTrashed();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('Administrador');
        }

        if ($panel->getId() === 'stock') {
            return $this->hasAnyRole(['Encargado de Stock', 'Administrador']);
        }

        if ($panel->getId() === 'vendedor') {
            return $this->hasAnyRole(['Vendedor', 'Administrador']);
        }

        return false;
    }

    /**
     * Sincroniza la ficha asociada (Cliente o Empleado) según los roles asignados al usuario.
     * Si no existe ficha física, se crea automáticamente para que aparezca en los listados del panel.
     */
    public function syncProfileFromRoles(array $extraData = []): void
    {
        // 1. Si posee rol 'Cliente' y aún no tiene ficha en customers:
        if ($this->hasRole('Cliente') && ! $this->customer) {
            $existingCustomer = Customer::where('email', $this->email)
                ->whereNull('user_id')
                ->first();

            if ($existingCustomer) {
                $existingCustomer->update(['user_id' => $this->id]);
            } else {
                $nameParts = explode(' ', trim($this->name), 2);
                $docNumber = $extraData['document_number'] ?? ('WEB-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT));
                $phone = $extraData['phone'] ?? '-';

                Customer::create([
                    'user_id'         => $this->id,
                    'first_name'      => $nameParts[0] ?: 'Cliente',
                    'last_name'       => $nameParts[1] ?? 'Web',
                    'document_number' => $docNumber,
                    'email'           => $this->email,
                    'phone'           => $phone,
                    'address'         => $extraData['address'] ?? 'Registrado vía Web',
                    'city_id'         => $extraData['city_id'] ?? 1,
                    'tax_condition'   => 'Consumidor Final',
                    'tax_id'          => $extraData['tax_id'] ?? '0',
                ]);
            }
        }

        // 2. Si posee rol de empleado (Vendedor, Encargado de Stock o Administrador) y no tiene ficha en employees:
        if ($this->hasAnyRole(['Administrador', 'Encargado de Stock', 'Vendedor']) && ! $this->employee) {
            $nameParts = explode(' ', trim($this->name), 2);
            $defaultCat = EmployeeCategory::first()?->id ?? 1;

            Employee::create([
                'user_id'              => $this->id,
                'first_name'           => $nameParts[0] ?: 'Empleado',
                'last_name'            => $nameParts[1] ?? 'Interno',
                'document_number'      => 'EMP-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
                'employee_category_id' => $defaultCat,
                'phone'                => '-',
                'address'              => 'Sede Central',
                'city_id'              => 1,
            ]);
        }
    }
}