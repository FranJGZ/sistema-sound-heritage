<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Event;
class EventSeeder extends Seeder {
    public function run() { 
        $events = [
            ['name' => 'Recital Banda Local: Los Tonos', 'date' => '2026-10-15', 'type' => 'Recital', 'capacity' => 100, 'venue_id' => 1],
            ['name' => 'Clínica de Guitarra Eléctrica', 'date' => '2026-11-05', 'type' => 'Taller/Clínica', 'capacity' => 50, 'venue_id' => 1],
            ['name' => 'Presentación de Disco Indie', 'date' => '2026-11-20', 'type' => 'Presentación', 'capacity' => 80, 'venue_id' => 1],
            ['name' => 'Taller de Síntesis Analógica', 'date' => '2026-12-02', 'type' => 'Taller/Clínica', 'capacity' => 50, 'venue_id' => 1],
            ['name' => 'Jam Session de Jazz', 'date' => '2026-12-10', 'type' => 'Encuentro Cultural', 'capacity' => 100, 'venue_id' => 1]
        ];
        foreach ($events as $event) { Event::create($event); }
    }
}