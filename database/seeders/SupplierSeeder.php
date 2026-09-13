<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
class SupplierSeeder extends Seeder {
    public function run() { 
        $suppliers = [
            ['name' => 'Distribuidora Norte', 'phone' => '111111', 'address' => 'Av. Uruguay 123'],
            ['name' => 'Bebidas del Litoral', 'phone' => '222222', 'address' => 'Ruta 12 Km 5'],
            ['name' => 'Carnes Premium', 'phone' => '333333', 'address' => 'Calle Buenos Aires 456'],
            ['name' => 'Limpieza Total', 'phone' => '444444', 'address' => 'Barrio Sur Manzana 2'],
            ['name' => 'Tecnología Ya', 'phone' => '555555', 'address' => 'Av. Corrientes 88']
        ];
        foreach ($suppliers as $sup) { Supplier::create($sup); }
    }
}