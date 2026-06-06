<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\EmployeeCategory;

class EmployeeCategorySeeder extends Seeder {
    public function run() { 
        $categories = [
            ['name' => 'Gerente'],
            ['name' => 'Vendedor'],
            ['name' => 'Cajero'],
            ['name' => 'Supervisor'],
            ['name' => 'Personal de Mantenimiento']
        ];
        foreach ($categories as $cat) { EmployeeCategory::create($cat); }
    }
}