<?php

namespace App\Livewire;

use App\Models\Event;
use App\Livewire\Concerns\HasCartActions;
use Livewire\Component;

class DetalleEvento extends Component
{
    use HasCartActions;

    public Event $evento;
    public int $cantidad = 1;
    public int $disponibles = 0;

    public function mount(int $id): void
    {
        $this->evento = Event::with(['venue', 'attendances'])->findOrFail($id);
        $ocupados = $this->evento->attendances->count();
        $this->disponibles = max(0, (int) $this->evento->capacity - $ocupados);
    }

    public function incrementar(): void
    {
        if ($this->cantidad < max(1, $this->disponibles)) {
            $this->cantidad++;
        }
    }

    public function decrementar(): void
    {
        if ($this->cantidad > 1) {
            $this->cantidad--;
        }
    }

    public function agregarAlCarrito(): void
    {
        if ($this->disponibles <= 0) {
            return;
        }

        $id = 'event_' . $this->evento->id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $nuevaCantidad = $carrito[$id]['cantidad'] + $this->cantidad;
            $carrito[$id]['cantidad'] = min($nuevaCantidad, $this->disponibles);
        } else {
            $carrito[$id] = [
                'name'                => $this->evento->name,
                'price'               => (float) $this->evento->price,
                'discount_percentage' => 0,
                'cantidad'            => min($this->cantidad, $this->disponibles),
                'image'               => null,
                'type'                => 'event',
                'date'                => $this->evento->date,
                'time'                => $this->evento->time,
                'venue'               => $this->evento->venue?->name ?? 'Auditorio Sound Heritage'
            ];
        }

        session()->put('carrito', $carrito);
        $totalCount = array_sum(array_column($carrito, 'cantidad'));
        $this->dispatch('cart-updated', count: $totalCount);
        $this->dispatch('open-cart');
    }

    public function render()
    {
        return view('livewire.detalle-evento', [
            'evento'      => $this->evento,
            'disponibles' => $this->disponibles,
        ]);
    }
}
