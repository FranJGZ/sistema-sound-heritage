<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $provinceMap = [
            'Misiones' => [
                'Posadas', 'Oberá', 'Eldorado', 'Puerto Iguazú', 'Apóstoles',
                'Leandro N. Alem', 'San Vicente', 'Montecarlo', 'Jardín América',
                'Puerto Rico', 'San Pedro', 'Candelaria', 'Aristóbulo del Valle',
                'Campo Grande', 'Wanda', 'Puerto Esperanza', 'San Javier', 'El Soberbio'
            ],
            'Corrientes' => [
                'Corrientes Capital', 'Goya', 'Paso de los Libres', 'Curuzú Cuatiá',
                'Mercedes', 'Santo Tomé', 'Bella Vista', 'Ituzaingó', 'Monte Caseros',
                'Esquina', 'Virasoro'
            ],
            'Chaco' => [
                'Resistencia', 'Presidencia Roque Sáenz Peña', 'Villa Ángela',
                'Barranqueras', 'Fontana', 'Castelli', 'Charata', 'General San Martín'
            ],
            'Formosa' => [
                'Formosa Capital', 'Clorinda', 'Pirané', 'El Colorado', 'Las Lomitas'
            ],
            'Entre Ríos' => [
                'Paraná', 'Concordia', 'Gualeguaychú', 'Concepción del Uruguay',
                'Gualeguay', 'Villaguay', 'Chajarí', 'Victoria', 'Colón', 'La Paz'
            ],
            'Ciudad Autónoma de Buenos Aires' => [
                'Ciudad Autónoma de Buenos Aires', 'Retiro', 'Palermo', 'Belgrano',
                'Recoleta', 'Caballito', 'Flores', 'San Telmo', 'Monserrat', 'Villa Urquiza'
            ],
            'Buenos Aires' => [
                'La Plata', 'Mar del Plata', 'Bahía Blanca', 'Tandil', 'San Nicolás',
                'Pergamino', 'Olavarría', 'Junín', 'Necochea', 'Zárate', 'Campana',
                'Quilmes', 'Lanús', 'San Isidro', 'Vicente López', 'Morón', 'Lomas de Zamora'
            ],
            'Córdoba' => [
                'Córdoba Capital', 'Villa Carlos Paz', 'Río Cuarto', 'Villa María',
                'San Francisco', 'Alta Gracia', 'Bell Ville', 'Jesús María', 'Río Tercero'
            ],
            'Santa Fe' => [
                'Rosario', 'Santa Fe Capital', 'Rafaela', 'Venado Tuerto', 'Reconquista',
                'Santo Tomé', 'Villa Gobernador Gálvez', 'Esperanza', 'Casilda', 'Cañada de Gómez'
            ],
            'Mendoza' => [
                'Mendoza Capital', 'San Rafael', 'Godoy Cruz', 'Guaymallén',
                'Las Heras', 'Luján de Cuyo', 'Maipú', 'San Martín', 'Tunuyán', 'Malargüe'
            ],
            'Tucumán' => [
                'San Miguel de Tucumán', 'Yerba Buena', 'Tafí Viejo', 'Concepción',
                'Aguilares', 'Banda del Río Salí', 'Monteros', 'Tafí del Valle'
            ],
            'Salta' => [
                'Salta Capital', 'San Ramón de la Nueva Orán', 'Tartagal', 'General Güemes',
                'Cafayate', 'Rosario de la Frontera', 'Metán', 'San Lorenzo'
            ],
            'Jujuy' => [
                'San Salvador de Jujuy', 'San Pedro de Jujuy', 'Palpalá', 'Libertador General San Martín',
                'Perico', 'Tilcara', 'Humahuaca', 'La Quiaca'
            ],
            'Santiago del Estero' => [
                'Santiago del Estero Capital', 'La Banda', 'Termas de Río Hondo',
                'Frías', 'Añatuya', 'Fernández', 'Quimilí'
            ],
            'San Juan' => [
                'San Juan Capital', 'Rawson', 'Rivadavia', 'Chimbas',
                'Santa Lucía', 'Pocito', 'Caucete', 'Jáchal'
            ],
            'San Luis' => [
                'San Luis Capital', 'Villa Mercedes', 'Merlo', 'La Punta', 'Juana Koslay'
            ],
            'Catamarca' => [
                'San Fernando del Valle de Catamarca', 'Valle Viejo', 'Belén',
                'Andalgalá', 'Recreo', 'Santa María', 'Tinogasta'
            ],
            'La Rioja' => [
                'La Rioja Capital', 'Chilecito', 'Aimogasta', 'Chamical', 'Chepes'
            ],
            'La Pampa' => [
                'Santa Rosa', 'General Pico', 'Toay', 'Eduardo Castex', 'General Acha'
            ],
            'Neuquén' => [
                'Neuquén Capital', 'San Martín de los Andes', 'Cutral Có', 'Plottier',
                'Zapala', 'Centenario', 'Villa La Angostura', 'Chos Malal'
            ],
            'Río Negro' => [
                'Viedma', 'San Carlos de Bariloche', 'General Roca', 'Cipolletti',
                'Villa Regina', 'Cinco Saltos', 'Catriel', 'El Bolsón'
            ],
            'Chubut' => [
                'Rawson', 'Comodoro Rivadavia', 'Trelew', 'Puerto Madryn', 'Esquel', 'Gaiman'
            ],
            'Santa Cruz' => [
                'Río Gallegos', 'Caleta Olivia', 'El Calafate', 'Pico Truncado',
                'Las Heras', 'Puerto Deseado', 'San Julián'
            ],
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur' => [
                'Ushuaia', 'Río Grande', 'Tolhuin'
            ],
        ];

        foreach ($provinceMap as $provName => $cities) {
            $province = Province::where('name', $provName)->first();
            if (!$province) {
                continue;
            }

            foreach ($cities as $cityName) {
                City::firstOrCreate([
                    'name'        => $cityName,
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}
