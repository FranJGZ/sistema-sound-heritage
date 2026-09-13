<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
class AttendanceSeeder extends Seeder {
    public function run() { 
        $attendances = [
            ['registration_date' => '2026-09-20', 'customer_id' => 4, 'event_id' => 1], // Melomana asiste a Recital
            ['registration_date' => '2026-10-01', 'customer_id' => 1, 'event_id' => 2], // Musico asiste a Clinica
            ['registration_date' => '2026-11-05', 'customer_id' => 3, 'event_id' => 3], // Productor asiste a Presentacion
            ['registration_date' => '2026-11-25', 'customer_id' => 5, 'event_id' => 4], // Bajista asiste a Taller Sintesis
            ['registration_date' => '2026-12-01', 'customer_id' => 2, 'event_id' => 5]  // Banda asiste a Jam Session
        ];
        foreach ($attendances as $att) { Attendance::create($att); }
    }
}