<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Recital en Vivo: Los Tonos',
                'date' => '2026-10-28',
                'time' => '21:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Show íntimo con amplificación valvular pura y repertorio de clásicos de rock nacional y blues.',
                'capacity' => 100,
                'price' => 5000.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Frecuencia Cero',
                'date' => '2026-11-04',
                'time' => '20:30:00',
                'type' => 'Recital en Vivo',
                'description' => 'Set eléctrico de guitarras angulares, texturas post-punk y sintetizadores analógicos en vivo.',
                'capacity' => 80,
                'price' => 4500.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Distorsión Vintage',
                'date' => '2026-11-12',
                'time' => '22:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Potencia y solos de guitarra con sonido clásico de los 70s y bases rítmicas contundentes.',
                'capacity' => 90,
                'price' => 4000.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: La Cofradía Sonora',
                'date' => '2026-11-19',
                'time' => '21:30:00',
                'type' => 'Recital en Vivo',
                'description' => 'Viaje musical progresivo con pasajes instrumentales, teclados Rhodes y guitarras acústicas.',
                'capacity' => 85,
                'price' => 4200.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Válvula 66',
                'date' => '2026-11-26',
                'time' => '21:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Noche de blues eléctrico de Chicago, armónica y sonido de luthería tradicional en directo.',
                'capacity' => 75,
                'price' => 3800.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Ámbar Quartet',
                'date' => '2026-12-03',
                'time' => '20:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Fusión de jazz contemporáneo, bajo fretless, piano de cola acústico y grooves envolventes.',
                'capacity' => 70,
                'price' => 4800.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Ecos del Tolex',
                'date' => '2026-12-10',
                'time' => '22:30:00',
                'type' => 'Recital en Vivo',
                'description' => 'Rock alternativo enérgico con guitarras distorsionadas y baterías potentes de auditorio.',
                'capacity' => 100,
                'price' => 4000.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Los Pasajes',
                'date' => '2026-12-17',
                'time' => '20:30:00',
                'type' => 'Recital en Vivo',
                'description' => 'Capas de reverberación analógica, melodías pop nostálgicas y arreglos de guitarras de 12 cuerdas.',
                'capacity' => 80,
                'price' => 4500.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Recital en Vivo: Ritual Analógico',
                'date' => '2026-12-24',
                'time' => '23:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Despedida de año con funk rock bailable, sección de vientos y percusiones calientes en el Live Room.',
                'capacity' => 120,
                'price' => 5500.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Masterclass de Calibración y Mantenimiento de Guitarras',
                'date' => '2026-10-31',
                'time' => '17:00:00',
                'type' => 'Taller/Clínica',
                'description' => 'Aprende a calibrar tu instrumento, ajuste de alma, radio de cejuela y entonación perfecta junto al maestro luthier.',
                'capacity' => 35,
                'price' => 3500.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Clínica de Tono, Ruteo y Pedales Analógicos',
                'date' => '2026-11-07',
                'time' => '18:30:00',
                'type' => 'Taller/Clínica',
                'description' => 'Estructura de ganancia, impedancias, cables y cómo ordenar tu pedalboard para un tono profesional de estudio.',
                'capacity' => 40,
                'price' => 3000.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Presentación de Disco: Raíces Eléctricas',
                'date' => '2026-11-15',
                'time' => '20:00:00',
                'type' => 'Presentación',
                'description' => 'Estreno exclusivo del nuevo álbum solista en cinta analógica con firma de vinilos y charla abierta con el autor.',
                'capacity' => 70,
                'price' => 4000.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Gran Jam Session y Tertulia de Músicos',
                'date' => '2026-11-21',
                'time' => '19:00:00',
                'type' => 'Encuentro Cultural',
                'description' => 'Encuentro acústico y eléctrico abierto para músicos de la comunidad. Traé tu instrumento o usá el backline del auditorio.',
                'capacity' => 90,
                'price' => 2500.00,
                'venue_id' => 1
            ],
            [
                'name' => 'Expo Luthería Independiente y Efectos de Boutique',
                'date' => '2026-11-28',
                'time' => '15:00:00',
                'type' => 'Feria',
                'description' => 'Feria de exhibición con más de 20 luthiers y fabricantes de amplificadores y micrófonos artesanales.',
                'capacity' => 150,
                'price' => 2000.00,
                'venue_id' => 1
            ],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(
                ['name' => $event['name']],
                $event
            );
        }
    }
}
