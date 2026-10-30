<?php

namespace Tests\Feature;

use App\Livewire\DetalleEvento;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DetalleEventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_evento_page_renders_successfully(): void
    {
        $venue = Venue::create([
            'name'     => 'Auditorio Sound Heritage',
            'location' => 'Av. Corrientes 1234',
            'capacity' => 150,
        ]);

        $event = Event::create([
            'venue_id'    => $venue->id,
            'name'        => 'Festival de Jazz Vintage',
            'type'        => 'Recital en Vivo',
            'date'        => now()->addDays(5)->format('Y-m-d'),
            'time'        => '21:00:00',
            'price'       => 7500,
            'capacity'    => 100,
            'description' => 'Tríos y cuartetos de jazz acústico en vivo.',
        ]);

        $response = $this->get(route('evento.detalle', $event->id));
        $response->assertOk();
        $response->assertSee('Festival de Jazz Vintage');
        $response->assertSee('Auditorio Sound Heritage');
        $response->assertSee('7.500');
        $response->assertSee('Pase nominal');
        $response->assertSee('Información de la Función');
    }

    public function test_can_add_event_passes_to_cart(): void
    {
        $venue = Venue::create([
            'name'     => 'Auditorio Sound Heritage',
            'location' => 'Av. Corrientes 1234',
            'capacity' => 100,
        ]);

        $event = Event::create([
            'venue_id'    => $venue->id,
            'name'        => 'Show de Blues & Soul',
            'type'        => 'Recital en Vivo',
            'date'        => now()->addDays(7)->format('Y-m-d'),
            'time'        => '21:30:00',
            'price'       => 6000,
            'capacity'    => 80,
            'description' => 'Noche de blues clásico.',
        ]);

        Livewire::test(DetalleEvento::class, ['id' => $event->id])
            ->call('incrementar')
            ->call('agregarAlCarrito')
            ->assertDispatched('open-cart');

        $carrito = session('carrito');
        $this->assertNotNull($carrito);
        $this->assertArrayHasKey('event_' . $event->id, $carrito);
        $this->assertEquals(2, $carrito['event_' . $event->id]['cantidad']);
        $this->assertEquals('Show de Blues & Soul', $carrito['event_' . $event->id]['name']);
    }
}

