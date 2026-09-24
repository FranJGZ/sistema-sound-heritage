<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Mary\Traits\Toast;

class HomeTienda extends Component

{
    use Toast;


    public function render()
    {
        return view('livewire.home-tienda', [
            'productos' => Product::where('stock', '>', 0)->get()
        ]);
    }

    public function agregarAlCarrito($id)
    {
        $producto = Product::find($id);

        if (!$producto) return;

        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
        } else {
            $carrito[$id] = [
                'name' => $producto->name,
                'price' => $producto->price,
                'cantidad' => 1,
                'image' => $producto->image_path
            ];
        }

        session()->put('carrito', $carrito);

        // Notificación flotante (Toast) de Mary UI
        $this->success(
            title: '¡Producto añadido!',
            description: "Se agregó {$producto->name} al carrito.",
            position: 'toast-bottom toast-end',
            icon: 'o-shopping-cart',
            css: 'alert-success',
            timeout: 3000
        );
    }
}
