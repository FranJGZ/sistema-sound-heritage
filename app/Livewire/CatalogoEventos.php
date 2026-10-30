<?php

namespace App\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
class CatalogoEventos extends Component
{
    use Toast;
    use WithPagination;
    use \App\Livewire\Concerns\HasCartActions;

    #[Url(as: 'categoria')]
    public ?string $categoria = null;

    #[Url(as: 'desde')]
    public ?string $dateFrom = null;

    #[Url(as: 'hasta')]
    public ?string $dateTo = null;

    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url(as: 'orden')]
    public string $sort = 'proximos'; // 'proximos', 'precio_asc', 'precio_desc'

    #[Url(as: 'vista')]
    public string $vista = 'grid'; // 'grid' | 'list'

    // Cantidades seleccionadas por evento para comprar entradas
    public array $cantidades = [];

    public function mount(): void
    {
        // Inicializar cantidades por defecto en 1
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
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function seleccionarCategoria(?string $cat): void
    {
        if ($this->categoria === $cat) {
            $this->categoria = null;
        } else {
            $this->categoria = $cat;
        }
        $this->resetPage();
    }

    public function limpiarFechas(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function limpiarTodo(): void
    {
        $this->categoria = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->search = '';
        $this->sort = 'proximos';
        $this->resetPage();
    }

    public function getCantidad(int $eventId): int
    {
        return $this->cantidades[$eventId] ?? 1;
    }

    public function incrementar(int $eventId, int $max = 10): void
    {
        $current = $this->getCantidad($eventId);
        if ($current < $max) {
            $this->cantidades[$eventId] = $current + 1;
        }
    }

    public function decrementar(int $eventId): void
    {
        $current = $this->getCantidad($eventId);
        if ($current > 1) {
            $this->cantidades[$eventId] = $current - 1;
        }
    }

    // =========================================================================
    // MÉTODOS DEL CARRITO UNIFICADO (REACTIVIDAD Y PERSISTENCIA EN SESIÓN)
    // =========================================================================
    public function agregarAlCarrito(int $id): void
    {
        $evento = Event::find($id);

        if (!$evento) {
            return;
        }

        $cantidad = $this->getCantidad($id);
        $cartKey = 'event_' . $evento->id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$cartKey])) {
            $carrito[$cartKey]['cantidad'] += $cantidad;
        } else {
            $carrito[$cartKey] = [
                'type'     => 'event',
                'event_id' => $evento->id,
                'name'     => 'Entrada: ' . $evento->name,
                'price'    => (float) $evento->price,
                'cantidad' => $cantidad,
                'date'     => $evento->date,
                'time'     => $evento->time,
                'image'    => null,
            ];
        }

        session()->put('carrito', $carrito);
        $this->cantidades[$id] = 1; // Restablecer selector a 1
        $totalCount = array_sum(array_column($carrito, 'cantidad'));
        $this->dispatch('cart-updated', count: $totalCount);
        $this->dispatch('open-cart');
    }

    // =========================================================================
    // CATEGORÍAS CULTURALES DEL ERP
    // =========================================================================
    public function getCategoriasEventosProperty(): array
    {
        return [
            'recital' => [
                'label' => 'Recitales y Conciertos',
                'types' => ['Recital en Vivo', 'Recital'],
            ],
            'clinica' => [
                'label' => 'Clínicas y Talleres',
                'types' => ['Taller/Clínica', 'Clínica', 'Taller'],
            ],
            'presentacion' => [
                'label' => 'Presentaciones de Disco',
                'types' => ['Presentación'],
            ],
            'encuentro' => [
                'label' => 'Encuentros Culturales',
                'types' => ['Encuentro Cultural'],
            ],
            'feria' => [
                'label' => 'Ferias de Audio y Luthería',
                'types' => ['Feria'],
            ],
        ];
    }

    public function render()
    {
        $categorias = $this->categoriasEventos;

        // Conteo de eventos por categoría
        $allEvents = Event::all();
        $categoryCounts = [];
        foreach ($categorias as $catKey => $catDef) {
            $categoryCounts[$catKey] = $allEvents->filter(function ($e) use ($catDef) {
                return in_array($e->type, $catDef['types']);
            })->count();
        }

        // Query principal
        $query = Event::with('venue');

        // Búsqueda textual
        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('type', 'like', $term);
            });
        }

        // Filtro de categoría
        if ($this->categoria && isset($categorias[$this->categoria])) {
            $query->whereIn('type', $categorias[$this->categoria]['types']);
        }

        // Filtros de fecha (Desde / Hasta)
        if (!empty($this->dateFrom)) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        // Ordenamiento
        if ($this->sort === 'precio_desc') {
            $query->orderByDesc('price');
        } elseif ($this->sort === 'precio_asc') {
            $query->orderBy('price');
        } else {
            // Próximos eventos primero
            $query->orderBy('date', 'asc')->orderBy('time', 'asc');
        }

        $eventos = $query->paginate(9);

        return view('livewire.catalogo-eventos', [
            'eventos'        => $eventos,
            'categorias'     => $categorias,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}

