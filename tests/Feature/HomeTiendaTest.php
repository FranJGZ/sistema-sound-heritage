<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\HomeTienda;

class HomeTiendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sound Heritage');
        $response->assertSee('Productos Destacados');
    }

    public function test_products_display_with_special_and_list_prices(): void
    {
        $product = Product::create([
            'name' => 'Guitarra Fender Stratocaster Test',
            'type' => 'cuerda',
            'price' => 100000.00,
            'stock' => 5,
            'brand' => 'Fender',
            'model' => 'Stratocaster',
            'custom_fields' => []
        ]);

        Livewire::test(HomeTienda::class)
            ->assertSee('Guitarra Fender Stratocaster Test')
            ->assertSee('100.000') // Precio de lista legal cargado en DB
            ->assertSee('90.000'); // Precio especial efectivo / transferencia 10% OFF
    }

    public function test_can_add_product_to_cart_and_modify_quantity(): void
    {
        $product = Product::create([
            'name' => 'Teclado Yamaha P-45 Test',
            'type' => 'teclado',
            'price' => 200000.00,
            'stock' => 3,
            'brand' => 'Yamaha',
            'model' => 'P-45',
            'custom_fields' => []
        ]);

        Livewire::test(HomeTienda::class)
            ->call('agregarAlCarrito', $product->id)
            ->assertDispatched('open-cart')
            ->call('incrementarCantidad', $product->id)
            ->call('decrementarCantidad', $product->id)
            ->call('eliminarDelCarrito', $product->id);

        $this->assertEmpty(session('carrito', []));
    }

    public function test_category_and_search_filters_work(): void
    {
        Product::create([
            'name' => 'Guitarra Gibson Les Paul',
            'type' => 'cuerda',
            'price' => 500000.00,
            'stock' => 2,
            'brand' => 'Gibson',
            'model' => 'Les Paul',
            'custom_fields' => []
        ]);

        Product::create([
            'name' => 'Batería Pearl Export',
            'type' => 'percusion',
            'price' => 400000.00,
            'stock' => 1,
            'brand' => 'Pearl',
            'model' => 'Export',
            'custom_fields' => []
        ]);

        // Filtro por categoría 'cuerda'
        Livewire::test(HomeTienda::class)
            ->set('selectedCategory', 'cuerda')
            ->assertViewHas('productos', function ($productos) {
                return $productos->contains('name', 'Guitarra Gibson Les Paul')
                    && ! $productos->contains('name', 'Batería Pearl Export');
            });

        // Búsqueda por término
        Livewire::test(HomeTienda::class)
            ->set('search', 'Pearl')
            ->assertViewHas('productos', function ($productos) {
                return $productos->contains('name', 'Batería Pearl Export')
                    && ! $productos->contains('name', 'Guitarra Gibson Les Paul');
            });
    }

    public function test_events_display_real_database_time_and_description(): void
    {
        $venue = \App\Models\Venue::firstOrCreate(
            ['id' => 1],
            ['name' => 'Auditorio Principal', 'location' => 'Av. Cultural 123', 'capacity' => 200]
        );

        \App\Models\Event::create([
            'name' => 'Masterclass de Luthería Acústica',
            'type' => 'Taller/Clínica',
            'date' => now()->addDays(5)->toDateString(),
            'time' => '19:45:00',
            'description' => 'Calibración de almas, maderas nobles y cejuelas de hueso.',
            'capacity' => 45,
            'price' => 4500.00,
            'venue_id' => $venue->id
        ]);

        Livewire::test(HomeTienda::class)
            ->assertSee('Masterclass de Luthería Acústica')
            ->assertSee('19:45 hs')
            ->assertSee('Calibración de almas, maderas nobles y cejuelas de hueso.')
            ->assertDontSee('21 Firmas Oficiales');
    }
}

