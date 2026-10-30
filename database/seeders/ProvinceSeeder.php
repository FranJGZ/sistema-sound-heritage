<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'Misiones',
            'Corrientes',
            'Chaco',
            'Formosa',
            'Entre Ríos',
            'Buenos Aires',
            'Ciudad Autónoma de Buenos Aires',
            'Córdoba',
            'Santa Fe',
            'Mendoza',
            'Tucumán',
            'Salta',
            'Jujuy',
            'Santiago del Estero',
            'San Juan',
            'San Luis',
            'Catamarca',
            'La Rioja',
            'La Pampa',
            'Neuquén',
            'Río Negro',
            'Chubut',
            'Santa Cruz',
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
        ];

        foreach ($provinces as $name) {
            Province::firstOrCreate(['name' => $name]);
        }
    }
}
