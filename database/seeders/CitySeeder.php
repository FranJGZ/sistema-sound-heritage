<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\City;
class CitySeeder extends Seeder {
    public function run() { 
        $cities = [['name' => 'Posadas', 'province_id' => 1], ['name' => 'Oberá', 'province_id' => 1], ['name' => 'Corrientes Capital', 'province_id' => 2], ['name' => 'Resistencia', 'province_id' => 3], ['name' => 'CABA', 'province_id' => 4]];
        foreach ($cities as $city) { City::create($city); }
    }
}