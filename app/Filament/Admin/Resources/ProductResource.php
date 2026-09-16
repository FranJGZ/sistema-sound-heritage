<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationLabel = 'Productos / Catálogo';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function getProductTypeOptions(): array
    {
        return [
            'cuerda'               => 'Instrumentos de Cuerda (Cordófonos)',
            'teclado'              => 'Teclados y Sintetizadores',
            'percusion'            => 'Percusión (Membranófonos e Idiófonos)',
            'viento'               => 'Instrumentos de Viento (Aerófonos)',
            'amplificacion'        => 'Equipos de Amplificación y Altavoces',
            'procesador_interfase' => 'Procesadores de Señal, Mezcladoras e Interfaces',
            'microfono'            => 'Micrófonos',
            'accesorio'            => 'Accesorios Generales',
        ];
    }

    public static function getProductSpecsFormSchema(): array
    {
        return [
            // 1. CORDÓFONOS (Guitarras, bajos, violines, ukeleles)
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Instrumentos de Cuerda')
                    ->schema([
                        Forms\Components\Select::make('specs.cuerda_tipo')
                            ->label('Tipo de Cuerda')
                            ->options([
                                'Nailon' => 'Nailon',
                                'Acero' => 'Acero',
                                'Níquel' => 'Níquel',
                                'Bronce' => 'Bronce',
                                'Entorchado plano' => 'Entorchado plano (Flatwound)',
                            ])
                            ->placeholder('Selecciona el tipo'),

                        Forms\Components\Select::make('specs.cuerda_cantidad')
                            ->label('Número de Cuerdas')
                            ->options([
                                '4' => '4 cuerdas (Bajo / Ukelele / Violín)',
                                '5' => '5 cuerdas (Bajo / Banjo)',
                                '6' => '6 cuerdas (Guitarra estándar)',
                                '7' => '7 cuerdas (Guitarra extendida)',
                                '8' => '8 cuerdas',
                                '12' => '12 cuerdas (Guitarra acústica/eléctrica)',
                            ]),

                        Forms\Components\Select::make('specs.orientacion')
                            ->label('Orientación')
                            ->options([
                                'Diestro' => 'Diestro',
                                'Zurdo' => 'Zurdo',
                                'Ambidiestro' => 'Ambidiestro',
                            ])
                            ->default('Diestro'),

                        Forms\Components\Select::make('specs.amplificacion')
                            ->label('Sistema de Amplificación')
                            ->options([
                                'Pasivo' => 'Pasivo',
                                'Activo' => 'Activo (requiere batería 9V)',
                                'Acústico puro' => 'Acústico puro (sin captador)',
                                'Piezoeléctrico' => 'Piezoeléctrico (electroacústico)',
                            ]),

                        Forms\Components\TextInput::make('specs.material_cuerpo')
                            ->label('Madera / Material del Cuerpo')
                            ->placeholder('Ej: Caoba, Arce, Palisandro, Aliso, Fibra de carbono')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'cuerda'),

            // 2. TECLADOS Y SINTETIZADORES
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Teclados y Sintetizadores')
                    ->schema([
                        Forms\Components\Select::make('specs.teclas_cantidad')
                            ->label('Número de Teclas')
                            ->options([
                                '25' => '25 teclas (2 octavas)',
                                '37' => '37 teclas (3 octavas)',
                                '49' => '49 teclas (4 octavas)',
                                '61' => '61 teclas (5 octavas)',
                                '73' => '73 teclas',
                                '76' => '76 teclas',
                                '88' => '88 teclas (Piano completo)',
                            ]),

                        Forms\Components\Select::make('specs.teclas_accion')
                            ->label('Acción de las Teclas')
                            ->options([
                                'Sintetizador' => 'Sintetizador (sin peso / liviana)',
                                'Semicontrapesada' => 'Semicontrapesada',
                                'Contrapesada' => 'Contrapesada (Acción de martillo / Piano real)',
                            ]),

                        Forms\Components\Select::make('specs.sensibilidad')
                            ->label('Sensibilidad al Tacto')
                            ->options([
                                'Velocidad' => 'Sensible a la velocidad (Velocity)',
                                'Velocidad + Aftertouch' => 'Sensible a velocidad y Aftertouch',
                                'Sin sensibilidad' => 'Sin respuesta a velocidad (tipo órgano)',
                            ]),

                        Forms\Components\TextInput::make('specs.polifonia')
                            ->label('Polifonía Máxima')
                            ->placeholder('Ej: 64, 128, 256 voces / Ilimitada'),

                        Forms\Components\TextInput::make('specs.conectividad_datos')
                            ->label('Conectividad de Datos')
                            ->placeholder('Ej: USB-MIDI, MIDI DIN 5 pines, Bluetooth MIDI')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'teclado'),

            // 3. PERCUSIÓN
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Percusión')
                    ->schema([
                        Forms\Components\Select::make('specs.percusion_tipo')
                            ->label('Tipo de Percusión')
                            ->options([
                                'Acústica' => 'Acústica',
                                'Electrónica' => 'Electrónica / Digital',
                            ]),

                        Forms\Components\TextInput::make('specs.piezas_cantidad')
                            ->label('Configuración / Piezas')
                            ->placeholder('Ej: Batería de 5 cuerpos, 4 pads + 3 platillos, Cajón 1 pieza'),

                        Forms\Components\Select::make('specs.parches_material')
                            ->label('Material de Parches / Almohadillas')
                            ->options([
                                'Mylar' => 'Mylar / Plástico estándar',
                                'Malla' => 'Malla (Mesh para electrónicas)',
                                'Goma' => 'Goma / Caucho',
                                'Cuero natural' => 'Cuero natural',
                            ]),

                        Forms\Components\TextInput::make('specs.casco_material')
                            ->label('Material del Vaso / Casco')
                            ->placeholder('Ej: Madera (Arce, Abedul), Metal (Acero, Latón), Plástico'),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'percusion'),

            // 4. INSTRUMENTOS DE VIENTO (AERÓFONOS)
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Instrumentos de Viento')
                    ->schema([
                        Forms\Components\Select::make('specs.afinacion')
                            ->label('Afinación Base')
                            ->options([
                                'Bb' => 'Si bemol (Bb)',
                                'Eb' => 'Mi bemol (Eb)',
                                'C' => 'Do (C)',
                                'F' => 'Fa (F)',
                                'G' => 'Sol (G)',
                                'Otra' => 'Otra afinación',
                            ]),

                        Forms\Components\TextInput::make('specs.digitacion')
                            ->label('Sistema de Digitación')
                            ->placeholder('Ej: Sistema Boehm, Sistema Alemán, Francés'),

                        Forms\Components\TextInput::make('specs.viento_material')
                            ->label('Material de Fabricación')
                            ->placeholder('Ej: Latón dorado, Plata, Madera de Ébano, Resina ABS'),

                        Forms\Components\Select::make('specs.boquilla_tipo')
                            ->label('Tipo de Boquilla')
                            ->options([
                                'Caña simple' => 'Caña simple (Saxofón / Clarinete)',
                                'Caña doble' => 'Caña doble (Oboe / Fagot)',
                                'Metal / Copa' => 'Boquilla metálica / Copa (Trompeta / Trombón)',
                            ]),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'viento'),

            // 5. EQUIPOS DE AMPLIFICACIÓN Y ALTAVOCES
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Amplificación y Altavoces')
                    ->schema([
                        Forms\Components\TextInput::make('specs.potencia_rms')
                            ->label('Potencia (RMS)')
                            ->placeholder('Ej: 50W RMS, 100W RMS continuos'),

                        Forms\Components\Select::make('specs.tecnologia_circuito')
                            ->label('Tecnología del Circuito')
                            ->options([
                                'Válvulas' => 'Válvulas (Tubos)',
                                'Estado sólido' => 'Estado sólido (Transistores)',
                                'Digital' => 'Digital / Modelado',
                                'Híbrido' => 'Híbrido (Válvula + Transistor)',
                            ]),

                        Forms\Components\TextInput::make('specs.altavoces_config')
                            ->label('Tamaño y Número de Altavoces')
                            ->placeholder('Ej: 1x12", 2x10", 1x15", 4x12"'),

                        Forms\Components\Select::make('specs.impedancia')
                            ->label('Impedancia de Salida')
                            ->options([
                                '4' => '4 Ω (Ohmios)',
                                '8' => '8 Ω (Ohmios)',
                                '16' => '16 Ω (Ohmios)',
                                'Conmutable' => 'Conmutable (4 / 8 / 16 Ω)',
                            ]),

                        Forms\Components\TextInput::make('specs.canales')
                            ->label('Canales Disponibles')
                            ->placeholder('Ej: 2 canales independientes con EQ propia')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'amplificacion'),

            // 6. PROCESADORES, MEZCLADORAS E INTERFACES
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Procesadores, Mezcladoras e Interfaces')
                    ->schema([
                        Forms\Components\TextInput::make('specs.entradas_salidas')
                            ->label('Entradas y Salidas (I/O)')
                            ->placeholder('Ej: 2x2, 4x4, 8x8, 16x4 canales físicos'),

                        Forms\Components\TextInput::make('specs.conectores_tipo')
                            ->label('Tipo de Conectores')
                            ->placeholder('Ej: XLR Canon, TRS/TS 1/4", RCA, ADAT, Combo XLR/TRS'),

                        Forms\Components\Select::make('specs.resolucion_audio')
                            ->label('Resolución de Audio')
                            ->options([
                                '24-bit / 192 kHz' => '24-bit / 192 kHz',
                                '24-bit / 96 kHz' => '24-bit / 96 kHz',
                                '24-bit / 48 kHz' => '24-bit / 48 kHz',
                                '32-bit Float' => '32-bit Float',
                            ]),

                        Forms\Components\Select::make('specs.phantom_power')
                            ->label('Alimentación Phantom (+48V)')
                            ->options([
                                'Sí' => 'Sí (+48V conmutable)',
                                'No' => 'No',
                            ]),

                        Forms\Components\TextInput::make('specs.compatibilidad')
                            ->label('Compatibilidad de Software')
                            ->placeholder('Ej: ASIO, CoreAudio, Windows/macOS/iOS')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'procesador_interfase'),

            // 7. MICRÓFONOS
            Forms\Components\Group::make([
                Forms\Components\Section::make('Especificaciones: Micrófonos')
                    ->schema([
                        Forms\Components\Select::make('specs.transduccion')
                            ->label('Principio de Transducción')
                            ->options([
                                'Dinámico' => 'Dinámico (Bobina móvil)',
                                'Condensador' => 'Condensador (Requiere phantom)',
                                'Cinta' => 'Cinta / Listón (Ribbon)',
                            ]),

                        Forms\Components\Select::make('specs.patron_polar')
                            ->label('Patrón Polar')
                            ->options([
                                'Cardioide' => 'Cardioide',
                                'Supercardioide' => 'Supercardioide',
                                'Hipercardioide' => 'Hipercardioide',
                                'Omnidireccional' => 'Omnidireccional',
                                'Figura en 8' => 'Figura en 8 (Bidireccional)',
                                'Multipatrón' => 'Multipatrón conmutable',
                            ]),

                        Forms\Components\TextInput::make('specs.respuesta_frecuencia')
                            ->label('Respuesta en Frecuencia')
                            ->placeholder('Ej: 20 Hz - 20 kHz, 50 Hz - 15 kHz'),

                        Forms\Components\TextInput::make('specs.spl_maximo')
                            ->label('SPL Máximo')
                            ->placeholder('Ej: 135 dB SPL, 145 dB SPL'),
                    ])
                    ->columns(2),
            ])
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'microfono'),

            // 8. ESPECIFICACIONES TRANSVERSALES (APLICA A TODO EL HARDWARE)
            Forms\Components\Section::make('🔌 Especificaciones Transversales (Hardware)')
                ->description('Datos físicos, eléctricos y accesorios incluidos.')
                ->schema([
                    Forms\Components\Select::make('specs.alimentacion')
                        ->label('Alimentación Eléctrica')
                        ->options([
                            '220V' => '220V AC (Red eléctrica)',
                            '110V/220V' => '110V / 220V (Conmutable)',
                            'Fuente Externa' => 'Fuente externa (9V / 12V / 18V DC)',
                            'Batería Litio' => 'Batería de litio recargable',
                            'Baterías AA/9V' => 'Baterías estándar (AA / AAA / 9V)',
                            'Bus USB' => 'Alimentación por Bus USB',
                            'Pasivo' => 'Pasivo / No requiere alimentación',
                        ]),

                    Forms\Components\TextInput::make('specs.dimensiones')
                        ->label('Dimensiones Físicas (Alto x Ancho x Profundidad)')
                        ->placeholder('Ej: 100 x 35 x 12 cm'),

                    Forms\Components\TextInput::make('specs.peso')
                        ->label('Peso Neto')
                        ->placeholder('Ej: 3.5 kg / 7.7 lbs'),

                    Forms\Components\Textarea::make('specs.accesorios_incluidos')
                        ->label('Accesorios Incluidos')
                        ->placeholder('Ej: Cables, estuche rígido, funda, llaves de afinación, manual')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(3),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General del Producto')
                    ->description('Datos principales para la identificación y venta del producto.')
                    ->icon('heroicon-m-cube')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Fender American Professional II Stratocaster')
                            ->columnSpan(2),

                        Forms\Components\Select::make('type')
                            ->label('Categoría / Tipo de Producto')
                            ->options(self::getProductTypeOptions())
                            ->required()
                            ->reactive()
                            ->default('cuerda')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('price')
                            ->label('Precio de Venta ($)')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stock Actual en Depósito')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                ...self::getProductSpecsFormSchema(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(fn ($state): string => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->weight(FontWeight::SemiBold)
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->weight(FontWeight::Bold)
                    ->color('secondary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cuerda' => 'Cuerdas',
                        'teclado' => 'Teclados',
                        'percusion' => 'Percusión',
                        'viento' => 'Viento',
                        'amplificacion' => 'Amplificación',
                        'procesador_interfase' => 'Procesadores / Audio',
                        'microfono' => 'Micrófonos',
                        'accesorio' => 'Accesorios',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cuerda', 'teclado' => 'primary',
                        'amplificacion', 'procesador_interfase' => 'secondary',
                        'microfono', 'percusion' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state > 5 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('primary'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}