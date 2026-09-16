<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
class SupplierSeeder extends Seeder {
    public function run() { 
        $suppliers = [
            ['name' => 'Fender Musical Instruments', 'contact_name' => 'Leo Fender Jr.', 'phone' => '3764673423', 'email' => 'ventas@fender.com', 'address' => 'Corona, California / Depósito Central'],
            ['name' => 'Yamaha Music Corp', 'contact_name' => 'Kenji Sato', 'phone' => '1155554444', 'email' => 'contacto@yamaha.com', 'address' => 'Av. Corrientes 88'],
            ['name' => 'Distribuidora Norte', 'contact_name' => 'Martín Benítez', 'phone' => '111111', 'email' => 'ventas@distribuidoranorte.com', 'address' => 'Av. Uruguay 123'],
            ['name' => 'Bebidas del Litoral', 'contact_name' => 'Mariana Silva', 'phone' => '222222', 'email' => 'pedidos@litoralbebidas.com', 'address' => 'Ruta 12 Km 5'],
            ['name' => 'Carnes Premium', 'contact_name' => 'Roberto Gómez', 'phone' => '333333', 'email' => 'comercial@carnespremium.com', 'address' => 'Calle Buenos Aires 456'],
        ];
        foreach ($suppliers as $sup) { Supplier::create($sup); }
    }
}