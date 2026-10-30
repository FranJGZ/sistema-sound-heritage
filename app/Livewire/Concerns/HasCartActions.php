<?php

namespace App\Livewire\Concerns;

trait HasCartActions
{
    public function getCartCountProperty(): int
    {
        $carrito = session('carrito', []);
        return array_sum(array_column($carrito, 'cantidad'));
    }

    public function getCartListSubtotalProperty(): float
    {
        return (float) collect(session('carrito', []))->sum(function ($item) {
            return ($item['price'] ?? 0) * ($item['cantidad'] ?? 1);
        });
    }

    public function getCartSavingsProperty(): float
    {
        return (float) collect(session('carrito', []))->sum(function ($item) {
            if (!empty($item['type']) && $item['type'] === 'event') {
                return 0;
            }
            $price = $item['price'] ?? 0;
            $qty = $item['cantidad'] ?? 1;
            $discountPct = !empty($item['discount_percentage']) ? ($item['discount_percentage'] / 100) : 0.10;
            return $price * $qty * $discountPct;
        });
    }

    public function getCartSubtotalProperty(): float
    {
        return max(0, $this->cartListSubtotal - $this->cartSavings);
    }

    public function incrementarCantidad(string|int $id): void
    {
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
            session()->put('carrito', $carrito);
            $totalCount = array_sum(array_column($carrito, 'cantidad'));
            $this->dispatch('cart-updated', count: $totalCount);
        }
    }

    public function decrementarCantidad(string|int $id): void
    {
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$id])) {
            if ($carrito[$id]['cantidad'] > 1) {
                $carrito[$id]['cantidad']--;
            } else {
                unset($carrito[$id]);
            }
            session()->put('carrito', $carrito);
            $totalCount = array_sum(array_column($carrito, 'cantidad'));
            $this->dispatch('cart-updated', count: $totalCount);
        }
    }

    public function eliminarDelCarrito(string|int $id): void
    {
        $carrito = session()->get('carrito', []);
        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
            $totalCount = array_sum(array_column($carrito, 'cantidad'));
            $this->dispatch('cart-updated', count: $totalCount);
        }
    }

    public function vaciarCarrito(): void
    {
        session()->forget('carrito');
        $this->dispatch('cart-updated', count: 0);
    }
}

