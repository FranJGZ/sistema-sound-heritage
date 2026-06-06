<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Province;
class ProvinceSeeder extends Seeder {
    public function run() { 
        $provinces = [['name' => 'Misiones'], ['name' => 'Corrientes'], ['name' => 'Chaco'], ['name' => 'Formosa'], ['name' => 'Entre Ríos']];
        foreach ($provinces as $prov) { Province::create($prov); }
    }
}