<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
class ReservationSeeder extends Seeder {
    public function run() { 
        $reservations = [
            ['type' => 'Ensayo de Banda', 'date' => '2026-09-01', 'status' => 'Confirmada', 'customer_id' => 2, 'venue_id' => 1],
            ['type' => 'Grabación Acústica', 'date' => '2026-10-15', 'status' => 'Pendiente', 'customer_id' => 1, 'venue_id' => 1],
            ['type' => 'Lanzamiento de Videoclip', 'date' => '2026-11-20', 'status' => 'Confirmada', 'customer_id' => 3, 'venue_id' => 1],
            ['type' => 'Masterclass Externa', 'date' => '2026-07-10', 'status' => 'Cancelada', 'customer_id' => 4, 'venue_id' => 1],
            ['type' => 'Ensayo General', 'date' => '2026-08-05', 'status' => 'Confirmada', 'customer_id' => 5, 'venue_id' => 1]
        ];
        foreach ($reservations as $res) { Reservation::create($res); }
    }
}