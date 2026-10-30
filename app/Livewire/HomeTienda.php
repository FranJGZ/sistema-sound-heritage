<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Event;
use Mary\Traits\Toast;

class HomeTienda extends Component
{
    use Toast, \App\Livewire\Concerns\HasCartActions;

    public string $search = '';
    public string $selectedCategory = 'cuerda';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => 'cuerda', 'as' => 'categoria']
    ];

    public function mount()
    {
        if (request()->query('verified') == '1' || session('status') === 'verified') {
            $this->success(
                title: '¡Correo verificado con éxito!',
                description: 'Tu cuenta ya está activa y lista para comprar y reservar.',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert-success text-white font-bold shadow-xl',
                timeout: 6000
            );
        }

        if (request()->query('google_logged_in') == '1') {
            $name = auth()->user()?->name ?? 'Usuario';
            $this->success(
                title: '¡Sesión iniciada con Google!',
                description: "¡Hola, {$name}! Ya podés explorar y operar en la tienda.",
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert-success text-white font-bold shadow-xl',
                timeout: 5000
            );
        }
    }

    public function seleccionarCategoria(string $categoria): void
    {
        $this->selectedCategory = $categoria;
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->selectedCategory = 'todas';
    }

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
                'name'     => $producto->name,
                'price'    => (float) $producto->price,
                'discount_percentage' => $producto->discount_percentage,
                'cantidad' => 1,
                'image'    => $producto->image ?? $producto->image_path,
                'type'     => 'product',
            ];
        }

        session()->put('carrito', $carrito);
        $totalCount = array_sum(array_column($carrito, 'cantidad'));

        $this->dispatch('cart-updated', count: $totalCount);
        $this->dispatch('open-cart');
    }

    public function render()
    {
        $query = Product::where('stock', '>', 0);

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('brand', 'like', $term)
                  ->orWhere('model', 'like', $term)
                  ->orWhere('type', 'like', $term);
            });
        }

        if (empty(trim($this->search))) {
            if ($this->selectedCategory === 'ofertas') {
                // Pestaña de ofertas del subnav: productos con mayor descuento o destacadas
                $query->orderByDesc('price');
            } else {
                $query->where('type', $this->selectedCategory);
            }
        }

        $categorias = [
            'cuerda'               => 'Guitarras y Cuerdas',
            'teclado'              => 'Teclados y Pianos',
            'percusion'            => 'Baterías y Percusión',
            'viento'               => 'Vientos',
            'amplificacion'        => 'Amplificadores',
            'procesador_interfase' => 'Audio y Estudio',
            'microfono'            => 'Micrófonos',
            'accesorio'            => 'Accesorios'
        ];

        // Productos destacados: rankeados automáticamente por los más vendidos reales en BD (18 productos para 3 slides de 6)
        $destacados = (clone $query)
            ->withSum('invoiceDetails', 'quantity')
            ->orderByDesc('invoice_details_sum_quantity')
            ->orderByDesc('id')
            ->take(18)
            ->get();

        // Últimas novedades (nuevos ingresos por fecha)
        $novedades = Product::where('stock', '>', 0)
            ->latest('id')
            ->take(6)
            ->get();

        // Próximos eventos culturales y recitales de bandas en vivo (9 en total para 3 slides de 3)
        $eventos = Event::with('venue')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take(9)
            ->get();

        if ($eventos->count() < 9) {
            $eventos = Event::with('venue')
                ->orderBy('date')
                ->take(9)
                ->get();
        }

        // Marcas oficiales con logos vectoriales SVG oficiales (dimensionados uniformemente en frontend)
        $marcas = [
            ['name' => 'Fender',          'cat' => 'Guitarras & Bajos',           'logo' => 'img/brands/fender.svg'],
            ['name' => 'Gibson',          'cat' => 'Guitarras de Colección',      'logo' => 'img/brands/gibson.svg'],
            ['name' => 'Marshall',        'cat' => 'Amplificación Valvular',      'logo' => 'img/brands/marshall.svg'],
            ['name' => 'Yamaha',          'cat' => 'Pianos & Teclados',           'logo' => 'img/brands/yamaha.svg'],
            ['name' => 'Roland',          'cat' => 'Baterías & Sintetizadores',   'logo' => 'img/brands/roland.svg'],
            ['name' => 'BOSS',            'cat' => 'Pedales & Procesadores',      'logo' => 'img/brands/boss.svg'],
            ['name' => 'Shure',           'cat' => 'Microfonía Profesional',      'logo' => 'img/brands/shure.svg'],
            ['name' => 'Ibanez',          'cat' => 'Guitarras & Bajos Eléctricos', 'logo' => 'img/brands/ibanez.svg'],
            ['name' => 'Audio-Technica',  'cat' => 'Auriculares & Vinilos',       'logo' => 'img/brands/audio-technica.svg'],
            ['name' => 'Korg',            'cat' => 'Workstations & Sintes',       'logo' => 'img/brands/korg.svg'],
            ['name' => 'Sennheiser',      'cat' => 'Microfonía & Monitoreo',      'logo' => 'img/brands/sennheiser.svg'],
            ['name' => 'C.F. Martin',     'cat' => 'Guitarras Acústicas',         'logo' => 'img/brands/martin.svg'],
            ['name' => 'Taylor',          'cat' => 'Guitarras Electroacústicas',  'logo' => 'img/brands/taylor.svg'],
            ['name' => 'VOX',             'cat' => 'Amplificación Británica',     'logo' => 'img/brands/vox.svg'],
            ['name' => 'Moog',            'cat' => 'Sintetizadores Analógicos',   'logo' => 'img/brands/moog.svg'],
            ['name' => 'Gretsch',         'cat' => 'Guitarras & Baterías Vintage', 'logo' => 'img/brands/gretsch.svg'],
            ['name' => 'Rickenbacker',    'cat' => 'Bajos & Guitarras de Culto',  'logo' => 'img/brands/rickenbacker.svg'],
            ['name' => 'Pearl',           'cat' => 'Baterías & Percusión',        'logo' => 'img/brands/pearl.svg'],
            ['name' => 'Ernie Ball',      'cat' => 'Cuerdas & Accesorios',        'logo' => 'img/brands/ernie-ball.svg'],
            ['name' => 'Pioneer DJ',      'cat' => 'Equipamiento DJ & Mezcla',    'logo' => 'img/brands/pioneer-dj.svg'],
            ['name' => 'AKG',             'cat' => 'Microfonía de Estudio',       'logo' => 'img/brands/akg.svg'],
        ];

        return view('livewire.home-tienda', [
            'productos'           => $query->latest()->get(),
            'productosDestacados' => $destacados,
            'ultimasNovedades'    => $novedades,
            'eventos'             => $eventos,
            'marcas'              => $marcas,
            'recomendados'        => Product::where('stock', '>', 0)->take(4)->get(),
            'categorias'          => $categorias,
            'carrito'             => session('carrito', [])
        ]);
    }
}
