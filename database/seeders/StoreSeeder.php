<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Store;
class StoreSeeder extends Seeder {
    public function run() { 
        $stores = [
            ['company_name' => 'Sound Heritage Casa Central',
            'address' => 'Calle Colón 1234',
            'tax_condition' => 'Responsable Inscripto', 
            'phone' => '3764123456', 
            'tax_id' => '30-11223344-5', 
            'start_date' => '2024-01-01']
        ];
        foreach ($stores as $store) { Store::create($store); }
    }
}