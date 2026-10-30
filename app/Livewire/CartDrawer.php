<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Concerns\HasCartActions;

class CartDrawer extends Component
{
    use HasCartActions;

    #[On('cart-updated')]
    public function onCartUpdated(): void
    {
        // Se dispara para re-renderizar de forma aislada este componente
    }

    public function getCartProperty(): array
    {
        return session('carrito', []);
    }

    public function getCartListSubtotalProperty(): float
    {
        $subtotal = 0;
        foreach ($this->cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['cantidad'] ?? 1);
        }
        return (float) $subtotal;
    }

    public function getCartSubtotalProperty(): float
    {
        $subtotal = 0;
        foreach ($this->cart as $item) {
            $isEvent = (!empty($item['type']) && $item['type'] === 'event');
            $discountPct = $isEvent ? 0 : (!empty($item['discount_percentage']) ? ($item['discount_percentage'] / 100) : 0.10);
            $finalPrice = ($item['price'] ?? 0) * (1 - $discountPct);
            $subtotal += $finalPrice * ($item['cantidad'] ?? 1);
        }
        return (float) $subtotal;
    }

    public function getCartSavingsProperty(): float
    {
        return max(0, $this->cartListSubtotal - $this->cartSubtotal);
    }

    public function render()
    {
        return view('livewire.cart-drawer');
    }
}

