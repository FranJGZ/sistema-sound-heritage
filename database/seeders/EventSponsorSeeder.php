<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\EventSponsor;
class EventSponsorSeeder extends Seeder {
    public function run() { 
        $links = [
            ['event_id' => 1, 'sponsor_id' => 3], 
            ['event_id' => 2, 'sponsor_id' => 1], 
            ['event_id' => 3, 'sponsor_id' => 2], 
            ['event_id' => 4, 'sponsor_id' => 5], 
            ['event_id' => 5, 'sponsor_id' => 4]  
        ];
        foreach ($links as $link) { EventSponsor::create($link); }
    }
}