<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{

    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se ubica primero (orden 1) en 'Tienda' porque el catálogo es la base de compras y ventas.
    // =========================================================================
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Productos / Catálogo';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    // Se desactiva SoftDeletingScope para que los artículos dados de baja sigan pudiendo consultarse en el inventario histórico
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // =========================================================================
    // 2. MÉTODOS AUXILIARES REUTILIZABLES (CATEGORÍAS Y FICHA TÉCNICA JSON)
    // Las claves ('cuerda', 'teclado', etc.) coinciden con la columna 'type' de la BD y se reutilizan en PurchaseResource.
    // =========================================================================
    public static function getProductTypeOptions(): array
    {
        return [
            'cuerda'               => 'Instrumentos de Cuerda',
            'teclado'              => 'Teclados y Sintetizadores',
            'percusion'            => 'Percusión',
            'viento'               => 'Instrumentos de Viento',
            'amplificacion'        => 'Equipos de Amplificación y Altavoces',
            'procesador_interfase' => 'Procesadores de Señal, Mezcladoras e Interfaces',
            'microfono'            => 'Micrófonos',
            'accesorio'            => 'Accesorios Generales',
        ];
    }

    // Se usa notación con punto ('specs.cuerda_tipo', etc.) para que Filament guarde los atributos dentro de la columna JSON 'specs'.
    // Cada sección evalúa visible() según la clave de categoría elegida para pedir solo los datos técnicos de esa familia.
    public static function getProductSpecsFormSchema(): array
    {
        return [
            // 1. INSTRUMENTOS DE CUERDA
            Forms\Components\Section::make('Especificaciones: Instrumentos de Cuerda')
                ->visible(fn (Get $get): bool => $get('type') === 'cuerda')
                ->schema([
                    Forms\Components\Select::make('specs.cuerda_tipo')
                        ->label('Tipo de Cuerda')
                        ->options([
                            'Nailon'           => 'Nailon',
                            'Acero'            => 'Acero',
                            'Níquel'           => 'Níquel',
                            'Bronce'           => 'Bronce',
                            'Entorchado plano' => 'Entorchado plano (Flatwound)',
                        ]),

                    Forms\Components\Select::make('specs.cuerda_cantidad')
                        ->label('Número de Cuerdas')
                        ->options([
                            '4'  => '4 cuerdas (Bajo / Ukelele / Violín)',
                            '5'  => '5 cuerdas (Bajo / Banjo)',
                            '6'  => '6 cuerdas (Guitarra estándar)',
                            '7'  => '7 cuerdas (Guitarra extendida)',
                            '8'  => '8 cuerdas',
                            '12' => '12 cuerdas',
                        ]),

                    Forms\Components\Select::make('specs.orientacion')
                        ->label('Orientación')
                        ->options([
                            'Diestro'     => 'Diestro',
                            'Zurdo'       => 'Zurdo',
                            'Ambidiestro' => 'Ambidiestro',
                        ])
                        ->default('Diestro'),

                    Forms\Components\Select::make('specs.amplificacion')
                        ->label('Sistema de Amplificación')
                        ->options([
                            'Pasivo'         => 'Pasivo',
                            'Activo'         => 'Activo (requiere batería 9V)',
                            'Acústico puro'  => 'Acústico puro (sin captador)',
                            'Piezoeléctrico' => 'Piezoeléctrico (electroacústico)',
                        ]),

                    Forms\Components\TextInput::make('specs.material_cuerpo')
                        ->label('Madera / Material del Cuerpo')
                        ->placeholder('Ej: Caoba, Arce, Palisandro, Aliso')
                        ->columnSpanFull(),
                ])->columns(2),

            // 2. TECLADOS Y SINTETIZADORES
            Forms\Components\Section::make('Especificaciones: Teclados y Sintetizadores')
                ->visible(fn (Get $get): bool => $get('type') === 'teclado')
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
                            'Sintetizador'    => 'Sintetizador (sin peso / liviana)',
                            'Semicontrapesada'=> 'Semicontrapesada',
                            'Contrapesada'    => 'Contrapesada (Acción de martillo / Piano real)',
                        ]),

                    Forms\Components\Select::make('specs.sensibilidad')
                        ->label('Sensibilidad al Tacto')
                        ->options([
                            'Velocidad'              => 'Sensible a la velocidad (Velocity)',
                            'Velocidad + Aftertouch' => 'Sensible a velocidad y Aftertouch',
                            'Sin sensibilidad'       => 'Sin respuesta a velocidad',
                        ]),

                    Forms\Components\TextInput::make('specs.polifonia')
                        ->label('Polifonía Máxima')
                        ->placeholder('Ej: 64, 128, 256 voces'),

                    Forms\Components\TextInput::make('specs.conectividad_datos')
                        ->label('Conectividad de Datos')
                        ->placeholder('Ej: USB-MIDI, MIDI DIN 5 pines, Bluetooth MIDI')
                        ->columnSpanFull(),
                ])->columns(2),

            // 3. PERCUSIÓN
            Forms\Components\Section::make('Especificaciones: Percusión')
                ->visible(fn (Get $get): bool => $get('type') === 'percusion')
                ->schema([
                    Forms\Components\Select::make('specs.percusion_naturaleza')
                        ->label('Tipo de Percusión')
                        ->options([
                            'Acústica'    => 'Acústica',
                            'Electrónica' => 'Electrónica / Digital',
                        ]),

                    Forms\Components\TextInput::make('specs.piezas_cantidad')
                        ->label('Configuración / Piezas')
                        ->placeholder('Ej: Batería de 5 cuerpos, Cajón 1 pieza'),

                    Forms\Components\Select::make('specs.parche_material')
                        ->label('Material de Parches / Almohadillas')
                        ->options([
                            'Mylar'         => 'Mylar / Plástico estándar',
                            'Malla (Mesh)'  => 'Malla (Mesh para electrónicas)',
                            'Goma'          => 'Goma / Caucho',
                            'Madera'        => 'Madera',
                            'Cuero natural' => 'Cuero natural',
                        ]),

                    Forms\Components\TextInput::make('specs.aleacion')
                        ->label('Material del Casco / Aleación')
                        ->placeholder('Ej: Arce, Abedul, Bronce B8, Acero'),
                ])->columns(2),

            // 4. INSTRUMENTOS DE VIENTO
            Forms\Components\Section::make('Especificaciones: Instrumentos de Viento')
                ->visible(fn (Get $get): bool => $get('type') === 'viento')
                ->schema([
                    Forms\Components\Select::make('specs.viento_familia')
                        ->label('Familia de Viento')
                        ->options([
                            'Madera (Caña)'   => 'Madera (Caña)',
                            'Madera (Bisel)'  => 'Madera (Bisel)',
                            'Metal (Boquilla)'=> 'Metal (Boquilla)',
                            'Lengüeta libre'  => 'Lengüeta libre',
                        ]),

                    Forms\Components\Select::make('specs.afinacion')
                        ->label('Afinación Base')
                        ->options([
                            'Si bemol (Bb)' => 'Si bemol (Bb)',
                            'Mi bemol (Eb)' => 'Mi bemol (Eb)',
                            'Do (C)'        => 'Do (C)',
                            'Fa (F)'        => 'Fa (F)',
                            'Sol (G)'       => 'Sol (G)',
                            'Otra'          => 'Otra afinación',
                        ]),

                    Forms\Components\TextInput::make('specs.digitacion')
                        ->label('Sistema de Digitación')
                        ->placeholder('Ej: Sistema Boehm, Sistema Alemán'),

                    Forms\Components\TextInput::make('specs.viento_material')
                        ->label('Material de Fabricación')
                        ->placeholder('Ej: Latón dorado, Plata, Madera de Ébano'),
                ])->columns(2),

            // 5. EQUIPOS DE AMPLIFICACIÓN Y ALTAVOCES
            Forms\Components\Section::make('Especificaciones: Amplificación y Altavoces')
                ->visible(fn (Get $get): bool => $get('type') === 'amplificacion')
                ->schema([
                    Forms\Components\TextInput::make('specs.potencia_rms')
                        ->label('Potencia (RMS)')
                        ->placeholder('Ej: 30W, 50W RMS, 650W'),

                    Forms\Components\Select::make('specs.tecnologia_amp')
                        ->label('Tecnología del Circuito')
                        ->options([
                            'Valvular'                    => 'Valvular (Tubos)',
                            'Estado Sólido (Transistores)'=> 'Estado Sólido (Transistores)',
                            'Modelado Digital'            => 'Modelado Digital',
                            'Bi-amplificado Clase D'      => 'Bi-amplificado Clase D',
                            'Clase D'                     => 'Clase D',
                        ]),

                    Forms\Components\TextInput::make('specs.altavoces_config')
                        ->label('Tamaño y Número de Altavoces')
                        ->placeholder('Ej: 1x12", 2x10", 1x15"'),

                    Forms\Components\Select::make('specs.impedancia')
                        ->label('Impedancia de Salida')
                        ->options([
                            '4'          => '4 Ω (Ohmios)',
                            '8'          => '8 Ω (Ohmios)',
                            '16'         => '16 Ω (Ohmios)',
                            'Conmutable' => 'Conmutable (4 / 8 / 16 Ω)',
                        ]),

                    Forms\Components\TextInput::make('specs.canales')
                        ->label('Canales Disponibles')
                        ->placeholder('Ej: 2 canales independientes con EQ propia')
                        ->columnSpanFull(),
                ])->columns(2),

            // 6. PROCESADORES, MEZCLADORAS E INTERFACES
            Forms\Components\Section::make('Especificaciones: Procesadores, Mezcladoras e Interfaces')
                ->visible(fn (Get $get): bool => $get('type') === 'procesador_interfase')
                ->schema([
                    Forms\Components\TextInput::make('specs.conectividad_pc')
                        ->label('Conectividad / Interfaz')
                        ->placeholder('Ej: USB-C, USB 2.0, Wi-Fi / Ethernet, Analógico'),

                    Forms\Components\TextInput::make('specs.canales_entrada')
                        ->label('Canales de Entrada')
                        ->placeholder('Ej: 2, 10, 18 canales'),

                    Forms\Components\TextInput::make('specs.procesamiento')
                        ->label('Tipo de Procesamiento')
                        ->placeholder('Ej: Digital DSP, Analógico, 24-bit / 192 kHz'),

                    Forms\Components\Select::make('specs.phantom_power')
                        ->label('Alimentación Phantom (+48V)')
                        ->options([
                            'Sí' => 'Sí (+48V conmutable)',
                            'No' => 'No',
                        ]),
                ])->columns(2),

            // 7. MICRÓFONOS
            Forms\Components\Section::make('Especificaciones: Micrófonos')
                ->visible(fn (Get $get): bool => $get('type') === 'microfono')
                ->schema([
                    Forms\Components\Select::make('specs.transductor_tipo')
                        ->label('Principio de Transducción')
                        ->options([
                            'Dinámico (Bobina móvil)'    => 'Dinámico (Bobina móvil)',
                            'Condensador (Requiere +48V)'=> 'Condensador (Requiere +48V)',
                            'Cinta / Listón (Ribbon)'    => 'Cinta / Listón (Ribbon)',
                        ]),

                    Forms\Components\Select::make('specs.patron_polar')
                        ->label('Patrón Polar')
                        ->options([
                            'Cardioide'       => 'Cardioide',
                            'Supercardioide'  => 'Supercardioide',
                            'Hipercardioide'  => 'Hipercardioide',
                            'Omnidireccional' => 'Omnidireccional',
                            'Bidireccional'   => 'Bidireccional',
                            'Multipatrón'     => 'Multipatrón',
                        ]),

                    Forms\Components\TextInput::make('specs.respuesta_frecuencia')
                        ->label('Respuesta en Frecuencia')
                        ->placeholder('Ej: 20 Hz - 20 kHz'),

                    Forms\Components\TextInput::make('specs.spl_maximo')
                        ->label('SPL Máximo')
                        ->placeholder('Ej: 135 dB SPL'),
                ])->columns(2),

            // 8. ACCESORIOS GENERALES
            Forms\Components\Section::make('Especificaciones: Accesorios Generales')
                ->visible(fn (Get $get): bool => $get('type') === 'accesorio')
                ->schema([
                    Forms\Components\Select::make('specs.subcategoria_accesorio')
                        ->label('Subcategoría de Accesorio')
                        ->options([
                            'Cuerdas y Encordados' => 'Cuerdas y Encordados',
                            'Cables y Conectores'  => 'Cables y Conectores',
                            'Soportes y Atriles'   => 'Soportes y Atriles',
                            'Fundas y Estuches'    => 'Fundas y Estuches',
                            'Púas y Correas'       => 'Púas y Correas',
                        ]),

                    Forms\Components\TextInput::make('specs.accesorios_incluidos')
                        ->label('Detalle / Medida / Calibre')
                        ->placeholder('Ej: .010-.046, 6 metros Plug-Plug, Acolchada'),
                ])->columns(2),
        ];
    }

    // Genera el reporte PDF de inventario y valorización respetando los filtros aplicados en pantalla
    public static function exportPdf($livewire)
    {
        $products   = $livewire->getFilteredSortedTableQuery()->get();
        $categories = self::getProductTypeOptions();

        // Se usa max(0, stock) por seguridad contable para que un eventual ajuste negativo nunca reste valor al inventario
        $totalValuation = $products->sum(fn ($product) => $product->price * max(0, $product->stock));

        $rows = [];
        foreach ($products as $product) {
            $rows[] = [
                $product->name,
                $categories[$product->type] ?? $product->type,
                '$' . number_format((float) $product->price, 2, ',', '.'),
                $product->stock . ' u.',
                '$' . number_format((float) ($product->price * max(0, $product->stock)), 2, ',', '.'),
                $product->trashed() ? 'De Baja' : 'Activo',
            ];
        }

        $pdf = Pdf::loadView('reports.pdf-generic', [
            'title'             => 'Reporte de Inventario y Catálogo de Productos',
            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
            'summary' => [
                'Total Productos Filtrados' => $products->count(),
                'Unidades Totales en Stock' => $products->sum('stock') . ' u.',
                'Stock Crítico / Agotado'   => $products->where('stock', '<=', 5)->count(),
                'Valorización de Stock'     => '$' . number_format((float) $totalValuation, 2, ',', '.'),
            ],
            'headers' => ['Producto', 'Categoría', 'Precio Unit.', 'Stock', 'Subtotal Valorizado', 'Estado'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'reporte-productos-' . date('Ymd-His') . '.pdf');
    }

    // =========================================================================
    // 3. FORMULARIO DE PRODUCTO (CREATE / EDIT)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información General del Producto')
                ->description('Datos principales para la identificación y venta del producto.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Producto')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('image')
                        ->label('Fotografía del Producto')
                        ->image()
                        ->directory('products')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->imageEditor()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('brand')
                        ->label('Marca / Fabricante')
                        ->maxLength(100)
                        ->placeholder('Ej: Fender, Yamaha, Gibson, Marshall')
                        ->datalist(fn () => Product::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand')->toArray()),

                    Forms\Components\TextInput::make('model')
                        ->label('Modelo')
                        ->maxLength(100)
                        ->placeholder('Ej: Stratocaster, Les Paul Standard, Pacifica'),

                    // Se marca live() para que el formulario redibuje por AJAX la sección de ficha técnica al cambiar la categoría
                    Forms\Components\Select::make('type')
                        ->label('Categoría / Tipo de Producto')
                        ->options(self::getProductTypeOptions())
                        ->default('cuerda')
                        ->live()
                        ->required(),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio de Venta ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(99999999.99)
                        ->validationMessages([
                            'max' => 'Precio excedido al máximo permitido ($99.999.999,99).',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Descuento de Oferta (% OFF)')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(99)
                        ->suffix('%')
                        ->placeholder('Ej: 15, 20, 30')
                        ->helperText('Dejar vacío para producto estándar. Si se ingresa un porcentaje, se publica en /ofertas.')
                        ->nullable(),

                    Forms\Components\TextInput::make('stock')
                        ->label('Stock Actual en Depósito')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->required(),
                ])->columns(4),

            ...self::getProductSpecsFormSchema(),
        ]);
    }

    // =========================================================================
    // 4. TABLA PRINCIPAL DE CATÁLOGO E INVENTARIO
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->square()
                    ->getStateUsing(function ($record) {
                        if (empty($record->image)) return null;
                        if (str_starts_with($record->image, 'http')) return $record->image;
                        if (str_starts_with($record->image, 'img/')) return asset($record->image);
                        return asset('storage/' . $record->image);
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        // Agrupa en un sub-where y castea el JSON a texto para que MySQL busque sin importar mayúsculas
                        return $query->where(function (Builder $sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%")
                                ->orWhereRaw('LOWER(CAST(specs AS CHAR)) LIKE ?', ['%' . mb_strtolower($search) . '%']);
                        });
                    })
                    ->description(function (Product $record): ?string {
                        // Muestra un resumen rápido de specs debajo del nombre para que el vendedor lo lea al toque
                        if (empty($record->specs)) {
                            return null;
                        }
                        return collect($record->specs)->take(3)->implode(' • ');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                // Traduce la clave interna ('cuerda', 'teclado') a su etiqueta legible para mostrarla en la tabla
                Tables\Columns\TextColumn::make('type')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::getProductTypeOptions()[$state] ?? ucfirst($state))
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Promo')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}% OFF" : '—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->sortable(),

                // Advierte en amarillo cuando quedan 5 unidades o menos y en rojo cuando el artículo se agotó
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default     => 'success',
                    })
                    ->sortable(),

                // Evalúa trashed() (deleted_at) para distinguir artículos vigentes de los dados de baja
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (Product $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->defaultSort('created_at', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA (100% ELOQUENT ORM)
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                // Por defecto muestra solo productos activos para no mezclar artículos discontinuados en la consulta diaria
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado en Catálogo')
                    ->placeholder('Solo Activos')
                    ->trueLabel('Todos (Activos + De Baja)')
                    ->falseLabel('Solo Dados de Baja'),

                Tables\Filters\Filter::make('solo_ofertas')
                    ->label('Solo en Oferta (% OFF)')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0)),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Categorías')
                    ->options(self::getProductTypeOptions())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('brand')
                    ->label('Marcas')
                    ->options(fn () => Product::query()->whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand', 'brand')->toArray())
                    ->searchable()
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('semaforo_stock')
                    ->label('Nivel de Stock')
                    ->options([
                        'normal'  => 'Stock Normal (> 5)',
                        'critico' => 'Stock Crítico (1 a 5)',
                        'agotado' => 'Agotados (0)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $selectedLevel = $data['value'] ?? null;

                        if ($selectedLevel === 'normal') {
                            $query->where('stock', '>', 5);
                        } elseif ($selectedLevel === 'critico') {
                            $query->whereBetween('stock', [1, 5]);
                        } elseif ($selectedLevel === 'agotado') {
                            $query->where('stock', '<=', 0);
                        }
                    }),

                // Atraviesa las relaciones con punto (producto -> detalle compra -> compra -> proveedor) para saber quién abastece cada artículo
                Tables\Filters\SelectFilter::make('proveedor')
                    ->label('Proveedor')
                    ->relationship('purchaseDetails.purchase.supplier', 'name')
                    ->searchable()
                    ->preload(),

                // Detecta mercadería inmovilizada cruzando el catálogo contra los renglones de facturas emitidas (invoiceDetails)
                Tables\Filters\TernaryFilter::make('rotacion')
                    ->label('Rotación de Ventas')
                    ->placeholder('Todos los productos')
                    ->trueLabel('Con ventas registradas')
                    ->falseLabel('Sin ventas (Stock inmovilizado)')
                    ->queries(
                        true: fn (Builder $query) => $query->has('invoiceDetails'),
                        false: fn (Builder $query) => $query->doesntHave('invoiceDetails'),
                    ),

                Tables\Filters\Filter::make('rango_precio')
                    ->form([
                        Forms\Components\TextInput::make('precio_min')
                            ->label('Precio Mínimo ($)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('precio_max')
                            ->label('Precio Máximo ($)')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['precio_min'])) {
                            $query->where('price', '>=', (float) $data['precio_min']);
                        }
                        if (! empty($data['precio_max'])) {
                            $query->where('price', '<=', (float) $data['precio_max']);
                        }
                    }),

                // Se compara contra null y '' porque el valor 0 es un número válido al buscar productos sin stock
                Tables\Filters\Filter::make('rango_stock')
                    ->form([
                        Forms\Components\TextInput::make('stock_min')
                            ->label('Stock Mínimo')
                            ->numeric(),
                        Forms\Components\TextInput::make('stock_max')
                            ->label('Stock Máximo')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['stock_min']) && $data['stock_min'] !== null && $data['stock_min'] !== '') {
                            $query->where('stock', '>=', (int) $data['stock_min']);
                        }
                        if (isset($data['stock_max']) && $data['stock_max'] !== null && $data['stock_max'] !== '') {
                            $query->where('stock', '<=', (int) $data['stock_max']);
                        }
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Alta desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Alta hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['desde'])) {
                            $query->whereDate('created_at', '>=', $data['desde']);
                        }
                        if (! empty($data['hasta'])) {
                            $query->whereDate('created_at', '<=', $data['hasta']);
                        }
                    }),
            ])
            ->filtersFormColumns(4)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)

            // -----------------------------------------------------------------
            // C) EXPORTACIÓN A PDF DEL LISTADO FILTRADO
            // -----------------------------------------------------------------
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(fn ($livewire) => self::exportPdf($livewire)),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (EDITAR, DAR DE BAJA Y REACTIVAR)
            // -----------------------------------------------------------------
            ->recordUrl(fn (Product $record): ?string => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Se usa baja lógica para retirar el artículo de la venta sin romper las facturas ni compras históricas donde figura
                    Tables\Actions\DeleteAction::make()
                        ->label('Dar de baja')
                        ->modalHeading('Dar de baja Producto')
                        ->modalDescription('¿Estás seguro de dar de baja este producto? Se ocultará de la tienda pero conservará su historial.')
                        ->hidden(fn (Product $record) => $record->trashed()),

                    Tables\Actions\RestoreAction::make()
                        ->label('Reactivar')
                        ->visible(fn (Product $record) => $record->trashed()),
                ]),
            ]);
    }

    // =========================================================================
    // 5. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}