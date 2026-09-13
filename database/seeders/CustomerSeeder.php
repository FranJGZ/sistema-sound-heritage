<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Customer;
class CustomerSeeder extends Seeder {
    public function run() { 
        $customers = [
            ['document_number' => '25111222', 'last_name' => 'Músico', 'first_name' => 'Luis', 'address' => 'Calle 10', 'phone' => '3764666666', 'tax_id' => '20-25111222-1', 'tax_condition' => 'Consumidor Final', 'email' => 'luis.musico@mail.com', 'city_id' => 1],
            ['document_number' => '28333444', 'last_name' => 'Banda', 'first_name' => 'Laura', 'address' => 'Av. 20', 'phone' => '3764777777', 'tax_id' => '27-28333444-2', 'tax_condition' => 'Responsable Inscripto', 'email' => 'laura.banda@mail.com', 'city_id' => 1],
            ['document_number' => '31555666', 'last_name' => 'Productor', 'first_name' => 'José', 'address' => 'Calle 30', 'phone' => '3764888888', 'tax_id' => '20-31555666-3', 'tax_condition' => 'Consumidor Final', 'email' => 'jose.prod@mail.com', 'city_id' => 2],
            ['document_number' => '34777888', 'last_name' => 'Melómana', 'first_name' => 'Sofía', 'address' => 'Av. 40', 'phone' => '3764999999', 'tax_id' => '27-34777888-4', 'tax_condition' => 'Exento', 'email' => 'sofia.vinyl@mail.com', 'city_id' => 1],
            ['document_number' => '37999000', 'last_name' => 'Bajista', 'first_name' => 'Diego', 'address' => 'Calle 50', 'phone' => '3765000000', 'tax_id' => '20-37999000-5', 'tax_condition' => 'Consumidor Final', 'email' => 'diego.bass@mail.com', 'city_id' => 3]
        ];
        foreach ($customers as $cust) { Customer::create($cust); }
    }
}