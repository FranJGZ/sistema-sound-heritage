<?php

namespace Tests\Feature;

use App\Livewire\CatalogoEventos;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogoEventosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Asegurar que existe al menos un venue para los eventos de prueba
        Venue::firstOrCreate(
            ['id' => 1],
            ['name' => 'Auditorio Principal', 'location' => 'Av. Corrientes 1234', 'capacity' => 150]
        );
    }

    public function test_eventos_page_renders_successfully(): void
    {
        $response = $this->get(route('eventos'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('catalogo-eventos');
        $response->assertSee('Cartelera de Eventos');
        $response->assertSee('Categorías');
        $response->assertSee('Filtrar por Fecha');
        $response->assertSee('Fecha Desde');
        $response->assertSee('Fecha Hasta');
    }

    public function test_displays_events_and_default_ordering(): void
    {
        $e1 = Event::firstOrCreate(
            ['name' => 'Concierto Acústico A'],
            [
                'date' => '2026-11-01',
                'time' => '20:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Concierto especial acústico.',
                'capacity' => 100,
                'price' => 3000,
                'venue_id' => 1,
            ]
        );

        $e2 = Event::firstOrCreate(
            ['name' => 'Concierto Eléctrico B'],
            [
                'date' => '2026-12-01',
                'time' => '21:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Concierto de rock.',
                'capacity' => 80,
                'price' => 5000,
                'venue_id' => 1,
            ]
        );

        Livewire::test(CatalogoEventos::class)
            ->assertSee($e1->name)
            ->assertSee($e2->name)
            ->assertSee('$ ' . number_format($e1->price, 0, ',', '.'));
    }

    public function test_can_filter_events_by_category(): void
    {
        Event::firstOrCreate(
            ['name' => 'Clínica Exclusiva de Bajos'],
            [
                'date' => '2026-11-10',
                'time' => '18:00:00',
                'type' => 'Taller/Clínica',
                'description' => 'Técnica de slap y grooves.',
                'capacity' => 40,
                'price' => 2800,
                'venue_id' => 1,
            ]
        );

        Livewire::test(CatalogoEventos::class)
            ->call('seleccionarCategoria', 'clinica')
            ->assertSet('categoria', 'clinica')
            ->assertSee('Clínica Exclusiva de Bajos')
            ->call('seleccionarCategoria', 'clinica') // Toggle off
            ->assertSet('categoria', null);
    }

    public function test_can_filter_events_by_date_range(): void
    {
        Event::firstOrCreate(
            ['name' => 'Evento Pasado Octubre'],
            [
                'date' => '2026-10-01',
                'time' => '19:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Fecha especial.',
                'capacity' => 50,
                'price' => 2000,
                'venue_id' => 1,
            ]
        );

        Event::firstOrCreate(
            ['name' => 'Evento Futuro Diciembre'],
            [
                'date' => '2026-12-20',
                'time' => '22:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Fecha de fin de año.',
                'capacity' => 100,
                'price' => 6000,
                'venue_id' => 1,
            ]
        );

        Livewire::test(CatalogoEventos::class)
            ->set('dateFrom', '2026-12-01')
            ->set('dateTo', '2026-12-31')
            ->assertSee('Evento Futuro Diciembre')
            ->assertDontSee('Evento Pasado Octubre')
            ->call('limpiarFechas')
            ->assertSet('dateFrom', null)
            ->assertSet('dateTo', null);
    }

    public function test_can_select_multiple_tickets_and_add_to_unified_cart(): void
    {
        $evento = Event::firstOrCreate(
            ['name' => 'Festival de Guitarras'],
            [
                'date' => '2026-11-25',
                'time' => '20:00:00',
                'type' => 'Recital en Vivo',
                'description' => 'Festival anual con solistas invitados.',
                'capacity' => 100,
                'price' => 4000,
                'venue_id' => 1,
            ]
        );

        session()->forget('carrito');

        Livewire::test(CatalogoEventos::class)
            ->call('incrementar', $evento->id)
            ->call('incrementar', $evento->id)
            ->assertSet('cantidades.' . $evento->id, 3)
            ->call('decrementar', $evento->id)
            ->assertSet('cantidades.' . $evento->id, 2)
            ->call('agregarAlCarrito', $evento->id)
            ->assertDispatched('open-cart');

        $carrito = session('carrito', []);
        $cartKey = 'event_' . $evento->id;

        $this->assertArrayHasKey($cartKey, $carrito);
        $this->assertEquals(2, $carrito[$cartKey]['cantidad']);
        $this->assertEquals('event', $carrito[$cartKey]['type']);
        $this->assertEquals(4000, $carrito[$cartKey]['price']);
        $this->assertEquals('Entrada: Festival de Guitarras', $carrito[$cartKey]['name']);
    }

    public function test_cart_drawer_actions_work_in_eventos(): void
    {
        $evento = Event::firstOrCreate(
            ['name' => 'Encuentro de Luthería y Músicos'],
            [
                'date' => '2026-11-30',
                'time' => '17:00:00',
                'type' => 'Encuentro Cultural',
                'description' => 'Encuentro con entrada general.',
                'capacity' => 80,
                'price' => 3500,
                'venue_id' => 1,
            ]
        );

        $cartKey = 'event_' . $evento->id;
        session()->put('carrito', [
            $cartKey => [
                'type'     => 'event',
                'event_id' => $evento->id,
                'name'     => 'Entrada: ' . $evento->name,
                'price'    => 3500.0,
                'cantidad' => 1,
                'date'     => $evento->date,
                'time'     => $evento->time,
            ]
        ]);

        Livewire::test(CatalogoEventos::class)
            ->call('incrementarCantidad', $cartKey);

        $this->assertEquals(2, session('carrito')[$cartKey]['cantidad']);

        Livewire::test(CatalogoEventos::class)
            ->call('decrementarCantidad', $cartKey);

        $this->assertEquals(1, session('carrito')[$cartKey]['cantidad']);

        Livewire::test(CatalogoEventos::class)
            ->call('eliminarDelCarrito', $cartKey);

        $this->assertArrayNotHasKey($cartKey, session('carrito', []));
    }
}
