<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; 
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permisoEditarRoles = Permission::firstOrCreate(['name' => 'editar_roles', 'guard_name' => 'web']);

        
        // Creamos los roles base del sistema
        
        $rol_admin = Role::firstOrCreate(['name' => 'Administrador']);
        $rol_cliente = Role::firstOrCreate(['name' => 'Cliente']);
        $rol_vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $rol_stock = Role::firstOrCreate(['name' => 'Encargado de Stock']);


        // Asignamos permisos a los roles
        $rol_admin->givePermissionTo($permisoEditarRoles);

    }
}
