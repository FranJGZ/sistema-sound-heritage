<?php

namespace Tests\Feature;

use App\Livewire\DetalleProducto;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DetalleProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_producto_page_renders_successfully(): void
    {
        $product = Product::create([
            'type'                => 'cuerda',
            'name'                => 'Guitarra Fender Stratocaster',
            'brand'               => 'Fender',
            'model'               => 'Player Series',
            'price'               => 1500000,
            'discount_percentage' => 15,
            'stock'               => 5,
            'specs'               => [
                'cuerda_tipo' => 'Níquel',
                'cuerdas'     => '6',
                'cuerpo'      => 'Aliso',
            ],
        ]);

        $response = $this->get(route('producto.detalle', $product->id));
        $response->assertOk();
        $response->assertSee('Guitarra Fender Stratocaster');
        $response->assertSee('Fender');
        $response->assertSee('Player Series');
        $response->assertSee('Stock disponible');
        $response->assertSee('Especificaciones Técnicas');
        $response->assertSee('Aliso');
    }

    public function test_can_increment_and_decrement_quantity_within_stock(): void
    {
        $product = Product::create([
            'type'                => 'cuerda',
            'name'                => 'Bajo Cort Action Bass',
            'brand'               => 'Cort',
            'model'               => 'Action Bass',
            'price'               => 450000,
            'discount_percentage' => 10,
            'stock'               => 3,
        ]);

        Livewire::test(DetalleProducto::class, ['id' => $product->id])
            ->assertSet('cantidad', 1)
            ->call('incrementar')
            ->assertSet('cantidad', 2)
            ->call('incrementar')
            ->assertSet('cantidad', 3)
            ->call('incrementar') // should not exceed stock of 3
            ->assertSet('cantidad', 3)
            ->call('decrementar')
            ->assertSet('cantidad', 2)
            ->call('decrementar')
            ->assertSet('cantidad', 1)
            ->call('decrementar') // should not go below 1
            ->assertSet('cantidad', 1);
    }

    public function test_adding_to_cart_stores_in_session_and_dispatches_event(): void
    {
        $product = Product::create([
            'type'                => 'teclado',
            'name'                => 'Sintetizador Korg Minilogue',
            'brand'               => 'Korg',
            'model'               => 'Minilogue XD',
            'price'               => 980000,
            'discount_percentage' => 10,
            'stock'               => 4,
        ]);

        Livewire::test(DetalleProducto::class, ['id' => $product->id])
            ->call('incrementar')
            ->call('agregarAlCarrito')
            ->assertDispatched('open-cart');

        $carrito = session('carrito');
        $this->assertNotNull($carrito);
        $this->assertArrayHasKey($product->id, $carrito);
        $this->assertEquals(2, $carrito[$product->id]['cantidad']);
        $this->assertEquals('Sintetizador Korg Minilogue', $carrito[$product->id]['name']);
    }
}

