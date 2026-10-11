<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Mary\Traits\Toast;

class HomeTienda extends Component

{
    use Toast;

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
