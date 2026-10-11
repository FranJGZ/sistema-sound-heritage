<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // 1-10: Instrumentos de Cuerda (cuerda)
            ['cuerda', 'Guitarra Eléctrica Fender Player Stratocaster', 1450000, 6, ['cuerda_tipo' => 'Níquel', 'cuerda_cantidad' => '6', 'orientacion' => 'Diestro', 'amplificacion' => 'Pasivo', 'material_cuerpo' => 'Aliso']],
            ['cuerda', 'Guitarra Eléctrica Gibson Les Paul Standard', 3600000, 2, ['cuerda_tipo' => 'Níquel', 'cuerda_cantidad' => '6', 'orientacion' => 'Diestro', 'amplificacion' => 'Pasivo', 'material_cuerpo' => 'Caoba']],
            ['cuerda', 'Guitarra Eléctrica Ibanez RG450DX', 890000, 0, ['cuerda_tipo' => 'Acero', 'cuerda_cantidad' => '6', 'orientacion' => 'Diestro', 'amplificacion' => 'Pasivo', 'material_cuerpo' => 'Meranti']],
            ['cuerda', 'Guitarra Criolla Yamaha C40 Clásica', 280000, 14, ['cuerda_tipo' => 'Nailon', 'cuerda_cantidad' => '6', 'orientacion' => 'Diestro', 'amplificacion' => 'Acústico puro', 'material_cuerpo' => 'Abeto']],
            ['cuerda', 'Guitarra Electroacústica Takamine GD20CE', 950000, 3, ['cuerda_tipo' => 'Bronce', 'cuerda_cantidad' => '6', 'orientacion' => 'Diestro', 'amplificacion' => 'Piezoeléctrico', 'material_cuerpo' => 'Cedro']],
            ['cuerda', 'Bajo Eléctrico Fender Precision Bass 4 Cuerdas', 1520000, 4, ['cuerda_tipo' => 'Níquel', 'cuerda_cantidad' => '4', 'orientacion' => 'Diestro', 'amplificacion' => 'Pasivo', 'material_cuerpo' => 'Aliso']],
            ['cuerda', 'Bajo Eléctrico Cort Action V Plus 5 Cuerdas', 640000, 1, ['cuerda_tipo' => 'Acero', 'cuerda_cantidad' => '5', 'orientacion' => 'Diestro', 'amplificacion' => 'Activo', 'material_cuerpo' => 'Álamo']],
            ['cuerda', 'Guitarra Eléctrica Squier Affinity Telecaster Zurda', 590000, 0, ['cuerda_tipo' => 'Níquel', 'cuerda_cantidad' => '6', 'orientacion' => 'Zurdo', 'amplificacion' => 'Pasivo', 'material_cuerpo' => 'Álamo']],
            ['cuerda', 'Ukelele Concierto Kala KA-15C Caoba', 145000, 12, ['cuerda_tipo' => 'Nailon', 'cuerda_cantidad' => '4', 'orientacion' => 'Ambidiestro', 'amplificacion' => 'Acústico puro', 'material_cuerpo' => 'Caoba']],
            ['cuerda', 'Violín Estudio Cremona SV-175 4/4', 420000, 3, ['cuerda_tipo' => 'Acero', 'cuerda_cantidad' => '4', 'orientacion' => 'Diestro', 'amplificacion' => 'Acústico puro', 'material_cuerpo' => 'Arce']],

            // 11-17: Teclados y Sintetizadores (teclado)
            ['teclado', 'Piano Digital Yamaha P-45B 88 Teclas Pesadas', 1100000, 5, ['teclas_cantidad' => '88', 'teclas_accion' => 'Contrapesada', 'polifonia' => '64']],
            ['teclado', 'Piano Digital Casio Privia PX-S1100', 1250000, 2, ['teclas_cantidad' => '88', 'teclas_accion' => 'Contrapesada', 'polifonia' => '192']],
            ['teclado', 'Sintetizador Analógico Korg Minilogue XD', 1380000, 1, ['teclas_cantidad' => '37', 'teclas_accion' => 'Sintetizador', 'polifonia' => '4']],
            ['teclado', 'Teclado Arreglador Yamaha PSR-E373 61 Teclas', 460000, 9, ['teclas_cantidad' => '61', 'teclas_accion' => 'Sintetizador', 'polifonia' => '48']],
            ['teclado', 'Controlador MIDI Arturia MiniLab 3 25 Teclas', 240000, 11, ['teclas_cantidad' => '25', 'teclas_accion' => 'Sintetizador', 'polifonia' => 'N/A']],
            ['teclado', 'Workstation Roland Fantom-06 61 Teclas', 2450000, 0, ['teclas_cantidad' => '61', 'teclas_accion' => 'Semicontrapesada', 'polifonia' => '256']],
            ['teclado', 'Controlador MIDI Novation Launchkey 49 MK3', 490000, 3, ['teclas_cantidad' => '49', 'teclas_accion' => 'Sintetizador', 'polifonia' => 'N/A']],

            // 18-23: Percusión (percusion)
            ['percusion', 'Batería Acústica Pearl Export EXX 5 Cuerpos', 1950000, 2, ['percusion_naturaleza' => 'Acústica', 'parche_material' => 'Mylar']],
            ['percusion', 'Batería Electrónica Alesis Nitro Max Mesh Kit', 1120000, 4, ['percusion_naturaleza' => 'Electrónica', 'parche_material' => 'Malla (Mesh)']],
            ['percusion', 'Set de Platillos Zildjian I Family Pro Gig Pack', 870000, 3, ['percusion_naturaleza' => 'Acústica', 'aleacion' => 'Bronce B8']],
            ['percusion', 'Cajón Peruano LP Aspire Acústico', 260000, 7, ['percusion_naturaleza' => 'Acústica', 'parche_material' => 'Madera']],
            ['percusion', 'Redoblante Tama Metalworks 14x5.5 Acero', 390000, 0, ['percusion_naturaleza' => 'Acústica', 'parche_material' => 'Mylar']],
            ['percusion', 'Octapad Roland SPD-SX PRO Sampling Pad', 1890000, 1, ['percusion_naturaleza' => 'Electrónica', 'parche_material' => 'Goma']],

            // 24-28: Instrumentos de Viento (viento)
            ['viento', 'Saxofón Alto Yamaha YAS-280 Dorado', 2300000, 2, ['viento_familia' => 'Madera (Caña)', 'afinacion' => 'Mi bemol (Eb)']],
            ['viento', 'Trompeta Bach TR300H2 Si Bemol', 1350000, 3, ['viento_familia' => 'Metal (Boquilla)', 'afinacion' => 'Si bemol (Bb)']],
            ['viento', 'Flauta Traversa Yamaha YFL-222 Plateada', 1150000, 0, ['viento_familia' => 'Madera (Bisel)', 'afinacion' => 'Do (C)']],
            ['viento', 'Armónica Hohner Marine Band 1896 en Do', 85000, 18, ['viento_familia' => 'Lengüeta libre', 'afinacion' => 'Do (C)']],
            ['viento', 'Clarinete Estudio Parquer Custom 17 Llaves', 480000, 5, ['viento_familia' => 'Madera (Caña)', 'afinacion' => 'Si bemol (Bb)']],

            // 29-35: Amplificación y Altavoces (amplificacion)
            ['amplificacion', 'Amplificador Guitarra Marshall MG30GFX 30W', 450000, 6, ['tecnologia_amp' => 'Estado Sólido (Transistores)', 'potencia_rms' => '30W']],
            ['amplificacion', 'Amplificador Valvular Fender Blues Junior IV 15W', 1680000, 1, ['tecnologia_amp' => 'Valvular', 'potencia_rms' => '15W']],
            ['amplificacion', 'Amplificador Guitarra Boss Katana-50 MKII', 690000, 5, ['tecnologia_amp' => 'Modelado Digital', 'potencia_rms' => '50W']],
            ['amplificacion', 'Amplificador de Bajo Ampeg RB-110 Rocket Bass 50W', 620000, 2, ['tecnologia_amp' => 'Estado Sólido (Transistores)', 'potencia_rms' => '50W']],
            ['amplificacion', 'Monitor de Estudio Activo KRK Rokit 5 G4', 430000, 8, ['tecnologia_amp' => 'Bi-amplificado Clase D', 'potencia_rms' => '55W']],
            ['amplificacion', 'Bafle Potenciado JBL EON715 15 Pulgadas 1300W', 1420000, 0, ['tecnologia_amp' => 'Clase D', 'potencia_rms' => '650W']],
            ['amplificacion', 'Amplificador Acústico Fishman Loudbox Mini 60W', 820000, 3, ['tecnologia_amp' => 'Estado Sólido (Transistores)', 'potencia_rms' => '60W']],

            // 36-41: Procesadores, Mezcladoras e Interfaces (procesador_interfase)
            ['procesador_interfase', 'Interfaz de Audio Focusrite Scarlett 2i2 4ta Gen', 380000, 12, ['conectividad_pc' => 'USB-C', 'canales_entrada' => '2']],
            ['procesador_interfase', 'Interfaz de Audio Universal Audio Volt 276', 610000, 3, ['conectividad_pc' => 'USB-C', 'canales_entrada' => '2']],
            ['procesador_interfase', 'Consola Mezcladora Yamaha MG10XU 10 Canales', 520000, 4, ['conectividad_pc' => 'USB 2.0', 'canales_entrada' => '10']],
            ['procesador_interfase', 'Pedalera Multiefectos Line 6 HX Stomp', 1390000, 1, ['conectividad_pc' => 'USB', 'procesamiento' => 'Digital DSP']],
            ['procesador_interfase', 'Pedal Overdrive Ibanez Tube Screamer TS9', 260000, 7, ['conectividad_pc' => 'Analógico', 'procesamiento' => 'Analógico']],
            ['procesador_interfase', 'Consola Digital Behringer X Air XR18 18 Canales', 1580000, 0, ['conectividad_pc' => 'Wi-Fi / Ethernet / USB', 'canales_entrada' => '18']],

            // 42-45: Micrófonos (microfono)
            ['microfono', 'Micrófono Dinámico Vocal Shure SM58-LC', 210000, 15, ['transductor_tipo' => 'Dinámico (Bobina móvil)', 'patron_polar' => 'Cardioide']],
            ['microfono', 'Micrófono de Instrumentos Shure SM57-LC', 205000, 8, ['transductor_tipo' => 'Dinámico (Bobina móvil)', 'patron_polar' => 'Cardioide']],
            ['microfono', 'Micrófono Condenser Audio-Technica AT2020', 235000, 2, ['transductor_tipo' => 'Condensador (Requiere +48V)', 'patron_polar' => 'Cardioide']],
            ['microfono', 'Micrófono de Estudio Rode NT1 5ta Generación', 590000, 0, ['transductor_tipo' => 'Condensador (Requiere +48V)', 'patron_polar' => 'Cardioide']],

            // 46-50: Accesorios Generales (accesorio)
            ['accesorio', 'Encordado Guitarra Eléctrica Ernie Ball Regular Slinky .010', 18500, 45, ['subcategoria_accesorio' => 'Cuerdas y Encordados']],
            ['accesorio', 'Encordado Guitarra Acústica DAddario EJ16 Phosphor Bronze', 22000, 30, ['subcategoria_accesorio' => 'Cuerdas y Encordados']],
            ['accesorio', 'Cable de Instrumento Ernie Ball Braided 6m Plug-Plug', 48000, 20, ['subcategoria_accesorio' => 'Cables y Conectores']],
            ['accesorio', 'Soporte Pie de Guitarra Hercules GS414B Plus', 95000, 3, ['subcategoria_accesorio' => 'Soportes y Atriles']],
            ['accesorio', 'Funda Acolchada Fender FE620 Guitarra Eléctrica', 135000, 1, ['subcategoria_accesorio' => 'Fundas y Estuches']],
        ];

        foreach ($catalog as $index => $item) {
            $daysAgo = (50 - $index) * 4;
            $isDeleted = in_array($index + 1, [8, 22, 41]); // 3 productos dados de baja para probar el filtro

            Product::create([
                'type'       => $item[0],
                'name'       => $item[1],
                'price'      => $item[2],
                'stock'      => $item[3],
                'specs'      => $item[4],
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays($daysAgo),
                'deleted_at' => $isDeleted ? now()->subDays(5) : null,
            ]);
        }
    }
}