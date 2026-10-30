<?php

namespace App\Livewire;

use App\Models\Product;
use App\Livewire\Concerns\HasCartActions;
use Livewire\Component;

class DetalleProducto extends Component
{
    use HasCartActions;

    public Product $producto;
    public int $cantidad = 1;
    public string $selectedImage = '';

    public function mount(int $id): void
    {
        $this->producto = Product::findOrFail($id);
        $this->selectedImage = $this->producto->image ?? $this->producto->image_path ?? '';
    }

    public function selectImage(string $image): void
    {
        $this->selectedImage = $image;
    }

    public function incrementar(): void
    {
        if ($this->cantidad < max(1, (int) $this->producto->stock)) {
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
        if ($this->producto->stock <= 0) {
            return;
        }

        $id = $this->producto->id;
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $nuevaCantidad = $carrito[$id]['cantidad'] + $this->cantidad;
            $carrito[$id]['cantidad'] = min($nuevaCantidad, (int) $this->producto->stock);
        } else {
            $carrito[$id] = [
                'name'                => $this->producto->name,
                'price'               => (float) $this->producto->price,
                'discount_percentage' => $this->producto->discount_percentage,
                'cantidad'            => min($this->cantidad, (int) $this->producto->stock),
                'image'               => $this->producto->image ?? $this->producto->image_path,
                'type'                => 'product'
            ];
        }

        session()->put('carrito', $carrito);
        $totalCount = array_sum(array_column($carrito, 'cantidad'));
        $this->dispatch('cart-updated', count: $totalCount);
        $this->dispatch('open-cart');
    }

    public function render()
    {
        return view('livewire.detalle-producto', [
            'producto' => $this->producto,
        ]);
    }
}
