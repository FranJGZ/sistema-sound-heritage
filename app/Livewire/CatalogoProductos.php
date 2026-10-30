<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class CatalogoProductos extends Component
{
    use Toast;
    use WithPagination;
    use \App\Livewire\Concerns\HasCartActions;

    #[Url(as: 'categoria')]
    public ?string $categoria = null;

    #[Url(as: 'subcategoria')]
    public ?string $subcategoria = null;

    public array $expandedCategories = [];

    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url(as: 'orden')]
    public string $sort = 'destacados'; // 'destacados', 'precio_desc', 'precio_asc'

    #[Url(as: 'vista')]
    public string $vista = 'grid'; // 'grid' | 'list'

    #[Url(as: 'marcas')]
    public array $selectedBrands = [];

    #[Url(as: 'specs')]
    public array $selectedSpecs = [];

    public bool $soloOfertas = false;

    public function mount(): void
    {
        if (request()->routeIs('ofertas')) {
            $this->soloOfertas = true;
        }
        if ($this->categoria && !in_array($this->categoria, $this->expandedCategories)) {
            $this->expandedCategories[] = $this->categoria;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingCategoria(): void
    {
        $this->resetPage();
        $this->subcategoria = null;
        $this->selectedBrands = [];
        $this->selectedSpecs = [];
    }

    public function toggleExpandCategory(string $cat): void
    {
        if (in_array($cat, $this->expandedCategories)) {
            $this->expandedCategories = array_values(array_diff($this->expandedCategories, [$cat]));
        } else {
            $this->expandedCategories[] = $cat;
        }
    }

    public function seleccionarCategoria(?string $cat): void
    {
        if ($this->categoria === $cat && empty($this->subcategoria)) {
            $this->categoria = null;
        } else {
            $this->categoria = $cat;
            if ($cat && !in_array($cat, $this->expandedCategories)) {
                $this->expandedCategories[] = $cat;
            }
        }
        $this->subcategoria = null;
        $this->selectedBrands = [];
        $this->selectedSpecs = [];
        $this->resetPage();
    }

    public function seleccionarSubcategoria(string $cat, string $subcat): void
    {
        $this->categoria = $cat;
        if ($this->subcategoria === $subcat) {
            $this->subcategoria = null;
        } else {
            $this->subcategoria = $subcat;
        }
        if (!in_array($cat, $this->expandedCategories)) {
            $this->expandedCategories[] = $cat;
        }
        $this->selectedBrands = [];
        $this->selectedSpecs = [];
        $this->resetPage();
    }

    public function toggleBrand(string $brand): void
    {
        if (in_array($brand, $this->selectedBrands)) {
            $this->selectedBrands = array_values(array_diff($this->selectedBrands, [$brand]));
        } else {
            $this->selectedBrands[] = $brand;
        }
        $this->resetPage();
    }

    public function setSpecFilter(string $key, ?string $value): void
    {
        if (empty($value)) {
            unset($this->selectedSpecs[$key]);
        } else {
            $this->selectedSpecs[$key] = $value;
        }
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->selectedBrands = [];
        $this->selectedSpecs = [];
        $this->search = '';
        $this->resetPage();
    }

    public function limpiarTodo(): void
    {
        $this->categoria = null;
        $this->subcategoria = null;
        $this->limpiarFiltros();
        $this->sort = 'destacados';
        $this->resetPage();
    }

    // =========================================================================
    // MÉTODOS DEL CARRITO (REACTIVIDAD Y PERSISTENCIA EN SESIÓN)
    // =========================================================================
    public function agregarAlCarrito(int $id): void
    {
        $producto = Product::find($id);

        if (!$producto) {
            return;
        }

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
        } else {
            $carrito[$id] = [
                'name'                => $producto->name,
                'price'               => (float) $producto->price,
                'discount_percentage' => $producto->discount_percentage,
                'cantidad'            => 1,
                'image'               => $producto->image ?? $producto->image_path
            ];
        }

        session()->put('carrito', $carrito);
        $totalCount = array_sum(array_column($carrito, 'cantidad'));
        $this->dispatch('cart-updated', count: $totalCount);
        $this->dispatch('open-cart');
    }

    // =========================================================================
    // ESQUEMA DE FILTROS TÉCNICOS EXCLUSIVOS POR TIPO (ESPECIFICACIONES ERP)
    // =========================================================================
    public function getSpecsSchemaProperty(): array
    {
        if (!$this->categoria) {
            return [];
        }

        $schemas = [
            'cuerda' => [
                'cuerda_tipo' => [
                    'label' => 'Tipo de Cuerda',
                    'options' => [
                        'Níquel' => 'Níquel',
                        'Acero' => 'Acero',
                        'Nailon' => 'Nailon',
                        'Bronce' => 'Bronce',
                        'Entorchado plano' => 'Entorchado plano (Flatwound)',
                    ],
                ],
                'cuerda_cantidad' => [
                    'label' => 'Número de Cuerdas',
                    'options' => [
                        '4' => '4 cuerdas (Bajo estándar)',
                        '5' => '5 cuerdas (Bajo / Banjo)',
                        '6' => '6 cuerdas (Guitarra estándar)',
                        '7' => '7 cuerdas (Guitarra extendida)',
                        '8' => '8 cuerdas',
                        '12' => '12 cuerdas',
                    ],
                ],
                'orientacion' => [
                    'label' => 'Orientación',
                    'options' => [
                        'Diestro' => 'Diestro',
                        'Zurdo' => 'Zurdo',
                        'Ambidiestro' => 'Ambidiestro',
                    ],
                ],
                'amplificacion' => [
                    'label' => 'Sistema de Amplificación',
                    'options' => [
                        'Pasivo' => 'Pasivo',
                        'Activo' => 'Activo (requiere batería 9V)',
                        'Acústico puro' => 'Acústico puro',
                        'Piezoeléctrico' => 'Piezoeléctrico',
                    ],
                ],
            ],
            'teclado' => [
                'teclas_cantidad' => [
                    'label' => 'Número de Teclas',
                    'options' => [
                        '25' => '25 teclas (2 octavas)',
                        '37' => '37 teclas (3 octavas)',
                        '49' => '49 teclas (4 octavas)',
                        '61' => '61 teclas (5 octavas)',
                        '73' => '73 teclas',
                        '76' => '76 teclas',
                        '88' => '88 teclas (Piano completo)',
                    ],
                ],
                'teclas_accion' => [
                    'label' => 'Acción de Teclas',
                    'options' => [
                        'Sintetizador' => 'Sintetizador (liviana)',
                        'Semicontrapesada' => 'Semicontrapesada',
                        'Contrapesada' => 'Contrapesada (Martillo / Piano)',
                    ],
                ],
                'sensibilidad' => [
                    'label' => 'Sensibilidad al Tacto',
                    'options' => [
                        'Velocidad' => 'Sensible a la velocidad',
                        'Velocidad + Aftertouch' => 'Velocidad + Aftertouch',
                        'Sin sensibilidad' => 'Sin respuesta a velocidad',
                    ],
                ],
            ],
            'percusion' => [
                'percusion_naturaleza' => [
                    'label' => 'Tipo de Percusión',
                    'options' => [
                        'Acústica' => 'Acústica Tradicional',
                        'Electrónica' => 'Electrónica / Digital',
                    ],
                ],
                'parche_material' => [
                    'label' => 'Material de Parches / Pads',
                    'options' => [
                        'Mylar' => 'Mylar / Plástico estándar',
                        'Malla (Mesh)' => 'Malla (Mesh para electrónicas)',
                        'Goma' => 'Goma / Caucho',
                        'Madera' => 'Madera',
                        'Cuero natural' => 'Cuero natural',
                    ],
                ],
            ],
            'viento' => [
                'viento_familia' => [
                    'label' => 'Familia de Viento',
                    'options' => [
                        'Madera (Caña)' => 'Madera (Caña)',
                        'Madera (Bisel)' => 'Madera (Bisel)',
                        'Metal (Boquilla)' => 'Metal (Boquilla)',
                        'Lengüeta libre' => 'Lengüeta libre',
                    ],
                ],
                'afinacion' => [
                    'label' => 'Afinación Base',
                    'options' => [
                        'Si bemol (Bb)' => 'Si bemol (Bb)',
                        'Mi bemol (Eb)' => 'Mi bemol (Eb)',
                        'Do (C)' => 'Do (C)',
                        'Fa (F)' => 'Fa (F)',
                        'Sol (G)' => 'Sol (G)',
                        'Otra' => 'Otra afinación',
                    ],
                ],
            ],
            'amplificacion' => [
                'tecnologia_amp' => [
                    'label' => 'Tecnología del Circuito',
                    'options' => [
                        'Valvular' => 'Valvular (Tubos)',
                        'Estado Sólido (Transistores)' => 'Estado Sólido (Transistores)',
                        'Modelado Digital' => 'Modelado Digital',
                        'Bi-amplificado Clase D' => 'Bi-amplificado Clase D',
                        'Clase D' => 'Clase D',
                    ],
                ],
                'impedancia' => [
                    'label' => 'Impedancia de Salida',
                    'options' => [
                        '4' => '4 Ω (Ohmios)',
                        '8' => '8 Ω (Ohmios)',
                        '16' => '16 Ω (Ohmios)',
                        'Conmutable' => 'Conmutable (4/8/16 Ω)',
                    ],
                ],
            ],
            'procesador_interfase' => [
                'conectividad_pc' => [
                    'label' => 'Conectividad / Interfaz',
                    'options' => [
                        'USB-C' => 'USB-C',
                        'USB 2.0' => 'USB 2.0',
                        'Analógico' => 'Analógico',
                        'Wi-Fi / Ethernet / USB' => 'Wi-Fi / Red',
                    ],
                ],
                'phantom_power' => [
                    'label' => 'Alimentación Phantom (+48V)',
                    'options' => [
                        'Sí' => 'Sí (+48V conmutable)',
                        'No' => 'No',
                    ],
                ],
            ],
            'microfono' => [
                'transductor_tipo' => [
                    'label' => 'Principio de Transducción',
                    'options' => [
                        'Dinámico (Bobina móvil)' => 'Dinámico (Bobina móvil)',
                        'Condensador (Requiere +48V)' => 'Condensador (+48V)',
                        'Cinta / Listón (Ribbon)' => 'Cinta / Listón (Ribbon)',
                    ],
                ],
                'patron_polar' => [
                    'label' => 'Patrón Polar',
                    'options' => [
                        'Cardioide' => 'Cardioide',
                        'Supercardioide' => 'Supercardioide',
                        'Hipercardioide' => 'Hipercardioide',
                        'Omnidireccional' => 'Omnidireccional',
                        'Bidireccional' => 'Bidireccional',
                        'Multipatrón' => 'Multipatrón',
                    ],
                ],
            ],
            'accesorio' => [
                'subcategoria_accesorio' => [
                    'label' => 'Subcategoría de Accesorio',
                    'options' => [
                        'Cuerdas y Encordados' => 'Cuerdas y Encordados',
                        'Cables y Conectores' => 'Cables y Conectores',
                        'Soportes y Atriles' => 'Soportes y Atriles',
                        'Fundas y Estuches' => 'Fundas y Estuches',
                        'Púas y Correas' => 'Púas y Correas',
                    ],
                ],
            ],
        ];

        return $schemas[$this->categoria] ?? [];
    }

    public function getSubcategoriasTaxonomiaProperty(): array
    {
        return [
            'cuerda' => [
                'guitarra_electrica' => [
                    'name' => 'Guitarra Eléctrica',
                    'keywords' => ['Guitarra Eléctrica', 'Stratocaster', 'Les Paul', 'Telecaster', 'Ibanez RG', 'Superstrat'],
                ],
                'guitarra_acustica' => [
                    'name' => 'Guitarra Acústica',
                    'keywords' => ['Guitarra Acústica', 'Guitarra Electroacústica', 'Takamine', 'Dreadnought'],
                ],
                'guitarra_criolla' => [
                    'name' => 'Guitarra Criolla',
                    'keywords' => ['Guitarra Criolla', 'Clásica', 'Yamaha C40'],
                ],
                'bajo' => [
                    'name' => 'Bajo Eléctrico',
                    'keywords' => ['Bajo Eléctrico', 'Precision Bass', 'Jazz Bass', 'Action V'],
                ],
                'ukelele' => [
                    'name' => 'Ukeleles',
                    'keywords' => ['Ukelele', 'Kala'],
                ],
                'violin' => [
                    'name' => 'Violines & Cuerdas Frotadas',
                    'keywords' => ['Violín', 'Cello', 'Cremona'],
                ],
            ],
            'teclado' => [
                'piano_digital' => [
                    'name' => 'Pianos Digitales',
                    'keywords' => ['Piano Digital', 'Privia', 'P-45', 'Clavinova'],
                ],
                'sintetizador' => [
                    'name' => 'Sintetizadores & Workstations',
                    'keywords' => ['Sintetizador', 'Workstation', 'Minilogue', 'Fantom', 'Juno'],
                ],
                'teclado_arreglador' => [
                    'name' => 'Teclados Arregladores',
                    'keywords' => ['Teclado Arreglador', 'PSR'],
                ],
                'controlador_midi' => [
                    'name' => 'Controladores MIDI',
                    'keywords' => ['Controlador MIDI', 'MiniLab', 'Launchkey', 'KeyLab', 'Keystation'],
                ],
            ],
            'percusion' => [
                'bateria_acustica' => [
                    'name' => 'Baterías Acústicas',
                    'keywords' => ['Batería Acústica', 'Pearl Export', 'Tama', 'Mapex'],
                ],
                'bateria_electronica' => [
                    'name' => 'Baterías Electrónicas',
                    'keywords' => ['Batería Electrónica', 'Alesis Nitro', 'V-Drums', 'Mesh Kit'],
                ],
                'platillos' => [
                    'name' => 'Platillos & Sets',
                    'keywords' => ['Platillos', 'Zildjian', 'Sabian', 'Paiste', 'Crash', 'Ride', 'Hi-Hat'],
                ],
                'percusion_tradicional' => [
                    'name' => 'Percusión Latina & Cajones',
                    'keywords' => ['Cajón', 'Conga', 'Bongó', 'Pandeiro', 'Timbal'],
                ],
                'percusion_digital' => [
                    'name' => 'Pads Digitales & Samplers',
                    'keywords' => ['Octapad', 'SPD-SX', 'Sample Pad', 'MultiPad'],
                ],
            ],
            'viento' => [
                'saxofon' => [
                    'name' => 'Saxofones',
                    'keywords' => ['Saxofón', 'Saxo', 'YAS-280'],
                ],
                'trompeta' => [
                    'name' => 'Trompetas & Bronces',
                    'keywords' => ['Trompeta', 'Trombón', 'Bach TR300'],
                ],
                'flauta' => [
                    'name' => 'Flautas Traversas',
                    'keywords' => ['Flauta', 'YFL-222'],
                ],
                'clarinete' => [
                    'name' => 'Clarinetes',
                    'keywords' => ['Clarinete'],
                ],
                'armonica' => [
                    'name' => 'Armónicas',
                    'keywords' => ['Armónica', 'Hohner Marine Band'],
                ],
            ],
            'amplificacion' => [
                'amp_guitarra' => [
                    'name' => 'Amplificadores de Guitarra',
                    'keywords' => ['Amplificador Guitarra', 'Katana', 'MG30', 'Champion'],
                ],
                'amp_valvular' => [
                    'name' => 'Amplificadores Valvulares',
                    'keywords' => ['Valvular', 'Blues Junior', 'AC30', 'JCM800'],
                ],
                'amp_bajo' => [
                    'name' => 'Amplificadores de Bajo',
                    'keywords' => ['Amplificador de Bajo', 'Rocket Bass', 'Ampeg', 'Rumble'],
                ],
                'bafles_monitores' => [
                    'name' => 'Bafles & Monitores',
                    'keywords' => ['Monitor de Estudio', 'Bafle', 'KRK Rokit', 'JBL EON'],
                ],
                'amp_acustico' => [
                    'name' => 'Amplificadores Acústicos',
                    'keywords' => ['Amplificador Acústico', 'Loudbox', 'Acoustasonic'],
                ],
            ],
            'procesador_interfase' => [
                'interfaz_audio' => [
                    'name' => 'Interfaces de Audio',
                    'keywords' => ['Interfaz de Audio', 'Scarlett', 'Volt', 'Komplete Audio'],
                ],
                'consola_mezcla' => [
                    'name' => 'Consolas & Mezcladoras',
                    'keywords' => ['Consola Mezcladora', 'Mezcladora', 'Yamaha MG'],
                ],
                'pedales_efectos' => [
                    'name' => 'Pedales & Pedaleras',
                    'keywords' => ['Pedal', 'Pedalera', 'HX Stomp', 'Tube Screamer', 'Overdrive'],
                ],
            ],
            'microfono' => [
                'mic_dinamico' => [
                    'name' => 'Micrófonos Dinámicos',
                    'keywords' => ['Micrófono Dinámico', 'SM58', 'SM57', 'e835'],
                ],
                'mic_condenser' => [
                    'name' => 'Micrófonos Condenser & Estudio',
                    'keywords' => ['Condenser', 'Condensador', 'AT2020', 'Rode NT1', 'U87'],
                ],
            ],
            'accesorio' => [
                'cuerdas_encordados' => [
                    'name' => 'Encordados & Cuerdas',
                    'keywords' => ['Encordado', 'Cuerdas', 'Slinky', 'EJ16'],
                ],
                'cables' => [
                    'name' => 'Cables & Conectores',
                    'keywords' => ['Cable', 'Plug-Plug', 'XLR'],
                ],
                'soportes' => [
                    'name' => 'Soportes & Pies',
                    'keywords' => ['Soporte', 'Pie de Guitarra', 'Hercules'],
                ],
                'fundas_estuches' => [
                    'name' => 'Fundas & Estuches',
                    'keywords' => ['Funda', 'Estuche', 'Fender FE620'],
                ],
            ],
        ];
    }

    public function render()
    {
        $categorias = [
            'cuerda'               => 'Instrumentos de Cuerda',
            'teclado'              => 'Teclados y Sintetizadores',
            'percusion'            => 'Baterías y Percusión',
            'viento'               => 'Instrumentos de Viento',
            'amplificacion'        => 'Equipos de Amplificación',
            'procesador_interfase' => 'Audio y Estudio',
            'microfono'            => 'Micrófonos',
            'accesorio'            => 'Accesorios Generales',
        ];

        // Conteo de productos por categoría
        $countQuery = Product::where('stock', '>', 0);
        if ($this->soloOfertas) {
            $countQuery->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0);
        }
        $categoryCounts = $countQuery
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Conteo de productos por subcategoría (cálculo en memoria ultra-rápido)
        $activeProductsQuery = Product::where('stock', '>', 0);
        if ($this->soloOfertas) {
            $activeProductsQuery->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0);
        }
        $allActiveProducts = $activeProductsQuery->get(['id', 'type', 'name', 'model']);
        $subcatCounts = [];
        foreach ($this->subcategoriasTaxonomia as $catKey => $subcats) {
            $catProducts = $allActiveProducts->where('type', $catKey);
            foreach ($subcats as $subKey => $subDef) {
                $subcatCounts[$catKey][$subKey] = $catProducts->filter(function ($p) use ($subDef) {
                    foreach ($subDef['keywords'] as $kw) {
                        if (stripos($p->name, $kw) !== false || stripos($p->model ?? '', $kw) !== false) {
                            return true;
                        }
                    }
                    return false;
                })->count();
            }
        }

        // Query principal
        $query = Product::where('stock', '>', 0);
        if ($this->soloOfertas) {
            $query->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0);
        }

        // Búsqueda textual
        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('brand', 'like', $term)
                  ->orWhere('model', 'like', $term)
                  ->orWhere('type', 'like', $term);
            });
        }

        // Filtro de categoría
        if ($this->categoria) {
            $query->where('type', $this->categoria);
        }

        // Filtro de subcategoría
        if ($this->categoria && $this->subcategoria) {
            $taxonomia = $this->subcategoriasTaxonomia;
            if (isset($taxonomia[$this->categoria][$this->subcategoria])) {
                $keywords = $taxonomia[$this->categoria][$this->subcategoria]['keywords'];
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhere('name', 'like', "%{$kw}%")
                          ->orWhere('model', 'like', "%{$kw}%");
                    }
                });
            }
        }

        // Filtro de marcas
        if (!empty($this->selectedBrands)) {
            $query->whereIn('brand', $this->selectedBrands);
        }

        // Filtro de especificaciones JSON del ERP
        if ($this->categoria && !empty($this->selectedSpecs)) {
            foreach ($this->selectedSpecs as $key => $val) {
                if (!empty($val)) {
                    $query->where("specs->{$key}", $val);
                }
            }
        }

        // Marcas disponibles según el filtro actual
        $brandQuery = Product::where('stock', '>', 0);
        if ($this->soloOfertas) {
            $brandQuery->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0);
        }
        if ($this->categoria) {
            $brandQuery->where('type', $this->categoria);
        }
        $availableBrands = $brandQuery->whereNotNull('brand')
            ->selectRaw('brand, count(*) as count')
            ->groupBy('brand')
            ->orderBy('brand')
            ->get();

        // Ordenamiento
        if ($this->sort === 'precio_desc') {
            $query->orderByDesc('price');
        } elseif ($this->sort === 'precio_asc') {
            $query->orderBy('price');
        } else {
            // Destacados: mayor volumen de ventas en invoiceDetails, o más reciente
            $query->withSum('invoiceDetails', 'quantity')
                  ->orderByDesc('invoice_details_sum_quantity')
                  ->orderByDesc('id');
        }

        $productos = $query->paginate(12);

        return view('livewire.catalogo-productos', [
            'productos'              => $productos,
            'categorias'             => $categorias,
            'categoryCounts'         => $categoryCounts,
            'subcategoriasTaxonomia' => $this->subcategoriasTaxonomia,
            'subcatCounts'           => $subcatCounts,
            'availableBrands'        => $availableBrands,
            'soloSoloOfertas'        => $this->soloOfertas,
        ]);
    }
}
