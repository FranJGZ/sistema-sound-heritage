<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Sponsor;
class SponsorSeeder extends Seeder {
    public function run() { 
        $sponsors = [
            ['name' => 'Yamaha Music', 'type' => 'Instrumentos', 'phone' => '112233', 'email' => 'sponsorship@yamaha.com'],
            ['name' => 'Radio Rock FM', 'type' => 'Prensa y Medios', 'phone' => '445566', 'email' => 'contacto@radiorock.com'],
            ['name' => 'Cervecería Artesanal Local', 'type' => 'Bebidas', 'phone' => '778899', 'email' => 'eventos@cerveza.com'],
            ['name' => 'Ernie Ball Strings', 'type' => 'Accesorios', 'phone' => '112211', 'email' => 'promo@ernieball.com'],
            ['name' => 'Productora Musical XYZ', 'type' => 'Productora', 'phone' => '334455', 'email' => 'info@xyz.com']
        ];
        foreach ($sponsors as $sponsor) { Sponsor::create($sponsor); }
    }
}