<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class SearchBar extends Component
{
    public string $search = '';
    public string $scope = 'all';

    public function mount(): void
    {
        $this->search = request()->query('search', '');
    }

    public function limpiar(): void
    {
        $this->search = '';
    }

    public function submitSearch(): void
    {
        $term = trim($this->search);
        if (!empty($term)) {
            $this->redirect(route('productos', ['search' => $term]), navigate: false);
        }
    }

    public function selectProduct(int $id): void
    {
        $this->redirect(route('producto.detalle', $id), navigate: false);
    }

    public function render()
    {
        $term = trim($this->search);
        $coincidencias = collect();

        if (mb_strlen($term) >= 2) {
            $query = Product::where('stock', '>', 0)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('brand', 'like', "%{$term}%")
                      ->orWhere('model', 'like', "%{$term}%");
                });

            if ($this->scope !== 'all') {
                $query->where('type', $this->scope);
            }

            $coincidencias = $query->take(6)->get();
        }

        return view('livewire.search-bar', [
            'coincidencias' => $coincidencias,
        ]);
    }
}

