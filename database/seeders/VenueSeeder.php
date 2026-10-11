<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venue;

class VenueSeeder extends Seeder
{
    public function run()
    {
        $venues = [
            [
                'name' => 'Sound Heritage Live Room',
                'capacity' => 100,
                'location' => 'Planta Baja'
            ]
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}