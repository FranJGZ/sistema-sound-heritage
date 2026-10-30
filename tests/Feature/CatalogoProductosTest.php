<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\CatalogoProductos;

class CatalogoProductosTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_page_renders_successfully(): void
    {
        $response = $this->get('/productos');
        $response->assertStatus(200);
        $response->assertSee('Sound Heritage');
        $response->assertSee('Destacados');
        $response->assertSee('Categorías');
    }

    public function test_default_view_displays_all_active_products_sorted_by_destacados(): void
    {
        $p1 = Product::create([
            'name' => 'Fender Stratocaster Sunburst',
            'type' => 'cuerda',
            'price' => 1000000.00,
            'stock' => 4,
            'brand' => 'Fender',
            'model' => 'Player II',
            'custom_fields' => [],
        ]);

        $p2 = Product::create([
            'name' => 'Sintetizador Roland Juno-DS',
            'type' => 'teclado',
            'price' => 800000.00,
            'stock' => 2,
            'brand' => 'Roland',
            'model' => 'Juno-DS61',
            'custom_fields' => [],
        ]);

        Livewire::test(CatalogoProductos::class)
            ->assertSee('Fender Stratocaster Sunburst')
            ->assertSee('Sintetizador Roland Juno-DS')
            ->assertSee('1.000.000') // Precio de lista
            ->assertSee('900.000')   // Precio de contado (10% OFF)
            ->assertSee('Sumar al carrito');
    }

    public function test_filters_only_activate_when_category_is_selected(): void
    {
        $p1 = Product::create([
            'name' => 'Bajo Fender Jazz Bass',
            'type' => 'cuerda',
            'price' => 1200000.00,
            'stock' => 3,
            'brand' => 'Fender',
            'specs' => ['cuerda_tipo' => 'Níquel', 'cuerda_cantidad' => '4'],
            'custom_fields' => [],
        ]);

        $p2 = Product::create([
            'name' => 'Teclado Korg Minilogue XD',
            'type' => 'teclado',
            'price' => 600000.00,
            'stock' => 5,
            'brand' => 'Korg',
            'specs' => ['teclas_cantidad' => '37'],
            'custom_fields' => [],
        ]);

        // Sin categoría seleccionada: muestra mensaje instructivo y NO muestra filtros técnicos
        Livewire::test(CatalogoProductos::class)
            ->assertSee('Filtros específicos')
            ->assertDontSee('Filtros Técnicos')
            ->call('seleccionarCategoria', 'cuerda')
            ->assertSee('Filtros Técnicos')
            ->assertSee('Tipo de Cuerda')
            ->assertSee('Número de Cuerdas')
            ->assertSee('Bajo Fender Jazz Bass')
            ->assertDontSee('Teclado Korg Minilogue XD');
    }

    public function test_dynamic_specs_filtering_from_erp(): void
    {
        Product::create([
            'name' => 'Guitarra Acero Acústica',
            'type' => 'cuerda',
            'price' => 400000.00,
            'stock' => 2,
            'brand' => 'Yamaha',
            'specs' => ['cuerda_tipo' => 'Acero'],
            'custom_fields' => [],
        ]);

        Product::create([
            'name' => 'Guitarra Criolla Nailon',
            'type' => 'cuerda',
            'price' => 300000.00,
            'stock' => 3,
            'brand' => 'Yamaha',
            'specs' => ['cuerda_tipo' => 'Nailon'],
            'custom_fields' => [],
        ]);

        Livewire::test(CatalogoProductos::class, ['categoria' => 'cuerda'])
            ->set('selectedSpecs', ['cuerda_tipo' => 'Nailon'])
            ->assertSee('Guitarra Criolla Nailon')
            ->assertDontSee('Guitarra Acero Acústica');
    }

    public function test_sort_dropdown_orders_by_price(): void
    {
        Product::create([
            'name' => 'Micrófono Shure SM58',
            'type' => 'microfono',
            'price' => 150000.00,
            'stock' => 10,
            'brand' => 'Shure',
            'custom_fields' => [],
        ]);

        Product::create([
            'name' => 'Micrófono Neumann U87',
            'type' => 'microfono',
            'price' => 3500000.00,
            'stock' => 1,
            'brand' => 'Neumann',
            'custom_fields' => [],
        ]);

        // Orden mayor precio
        Livewire::test(CatalogoProductos::class, ['sort' => 'precio_desc'])
            ->assertSeeInOrder(['Micrófono Neumann U87', 'Micrófono Shure SM58']);

        // Orden menor precio
        Livewire::test(CatalogoProductos::class, ['sort' => 'precio_asc'])
            ->assertSeeInOrder(['Micrófono Shure SM58', 'Micrófono Neumann U87']);
    }

    public function test_can_add_to_cart_from_catalogo(): void
    {
        $product = Product::create([
            'name' => 'Amplificador Fender Deluxe Reverb',
            'type' => 'amplificacion',
            'price' => 2500000.00,
            'stock' => 2,
            'brand' => 'Fender',
            'custom_fields' => [],
        ]);

        Livewire::test(CatalogoProductos::class)
            ->call('agregarAlCarrito', $product->id)
            ->assertDispatched('open-cart');

        $carrito = session('carrito', []);
        $this->assertArrayHasKey($product->id, $carrito);
        $this->assertEquals(1, $carrito[$product->id]['cantidad']);
    }

    public function test_subcategories_can_expand_and_filter_products(): void
    {
        Product::create([
            'name' => 'Guitarra Acústica Takamine GD20CE',
            'type' => 'cuerda',
            'price' => 700000.00,
            'stock' => 2,
            'brand' => 'Takamine',
            'custom_fields' => [],
        ]);

        Product::create([
            'name' => 'Bajo Eléctrico Fender Jazz Bass',
            'type' => 'cuerda',
            'price' => 1200000.00,
            'stock' => 1,
            'brand' => 'Fender',
            'custom_fields' => [],
        ]);

        Livewire::test(CatalogoProductos::class)
            ->call('toggleExpandCategory', 'cuerda')
            ->assertSee('Guitarra Acústica')
            ->assertSee('Bajo Eléctrico')
            ->call('seleccionarSubcategoria', 'cuerda', 'guitarra_acustica')
            ->assertSee('Guitarra Acústica Takamine GD20CE')
            ->assertDontSee('Bajo Eléctrico Fender Jazz Bass');
    }

    public function test_ofertas_route_only_displays_products_with_discount_percentage_and_shows_savings_label(): void
    {
        // Producto estándar sin oferta
        Product::create([
            'name' => 'Guitarra Normal Sin Oferta',
            'type' => 'cuerda',
            'price' => 500000.00,
            'stock' => 3,
            'brand' => 'Yamaha',
            'discount_percentage' => null,
            'custom_fields' => [],
        ]);

        // Producto en oferta con 20% de descuento ($100.000 de ahorro sobre $500.000)
        Product::create([
            'name' => 'Guitarra Eléctrica En Promo Especial',
            'type' => 'cuerda',
            'price' => 500000.00,
            'stock' => 2,
            'brand' => 'Ibanez',
            'discount_percentage' => 20,
            'custom_fields' => [],
        ]);

        $response = $this->get('/ofertas');
        $response->assertStatus(200);
        $response->assertSee('Ofertas');
        $response->assertSee('Guitarra Eléctrica En Promo Especial');
        $response->assertSee('20% OFF');
        $response->assertSee('Ahorrás $100.000');
        $response->assertDontSee('Guitarra Normal Sin Oferta');
    }
}
