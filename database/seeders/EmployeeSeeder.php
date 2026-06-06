<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Employee;
class EmployeeSeeder extends Seeder {
    public function run() { 
        $employees = [
            ['document_number' => '30111222', 'last_name' => 'Pérez', 'first_name' => 'Juan', 'address' => 'Av. Mitre 123', 'phone' => '3764111111', 'city_id' => 1, 'employee_category_id' => 1],
            ['document_number' => '32333444', 'last_name' => 'Gómez', 'first_name' => 'Ana', 'address' => 'Calle Junín 456', 'phone' => '3764222222', 'city_id' => 1, 'employee_category_id' => 2],
            ['document_number' => '35555666', 'last_name' => 'López', 'first_name' => 'Carlos', 'address' => 'Barrio Sur', 'phone' => '3764333333', 'city_id' => 1, 'employee_category_id' => 3],
            ['document_number' => '38777888', 'last_name' => 'Díaz', 'first_name' => 'Marta', 'address' => 'Av. Tambor', 'phone' => '3764444444', 'city_id' => 1, 'employee_category_id' => 4],
            ['document_number' => '40999000', 'last_name' => 'Ruiz', 'first_name' => 'Pedro', 'address' => 'Villa Cabello', 'phone' => '3764555555', 'city_id' => 1, 'employee_category_id' => 5]
        ];
        foreach ($employees as $emp) { Employee::create($emp); }
    }
}