<?php

namespace Tests\Feature;

use App\Livewire\ReservarSalon;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReservarSalonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Venue::firstOrCreate(
            ['id' => 1],
            ['name' => 'Auditorio Sound Heritage', 'location' => 'Av. Corrientes 1234, CABA', 'capacity' => 150]
        );

        \App\Models\Province::firstOrCreate(
            ['id' => 1],
            ['name' => 'Buenos Aires']
        );

        \App\Models\City::firstOrCreate(
            ['id' => 1],
            ['name' => 'CABA', 'province_id' => 1]
        );

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
    }

    public function test_reservar_salon_page_renders_successfully(): void
    {
        $response = $this->get(route('reservar-salon'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('reservar-salon');
        $response->assertSee('Solicitud de Reserva de Salón');
        $response->assertSee('Motivo del Alquiler');
        $response->assertSee('Resumen de Alquiler');
    }

    public function test_calculates_price_and_tier_discounts_correctly(): void
    {
        // 4 personas: Tarifa plena (0% OFF)
        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 4)
            ->assertSet('basePrice', 35000.0)
            ->assertSet('discountPercent', 0)
            ->assertSet('totalPrice', 35000.0);

        // 5 personas: 10% OFF
        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 5)
            ->assertSet('discountPercent', 10)
            ->assertSet('discountAmount', 3500.0)
            ->assertSet('totalPrice', 31500.0);

        // 10 personas: 15% OFF
        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 10)
            ->assertSet('discountPercent', 15)
            ->assertSet('totalPrice', 29750.0);

        // 15 personas: 20% OFF
        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 15)
            ->assertSet('discountPercent', 20)
            ->assertSet('totalPrice', 28000.0);

        // 20 personas: 25% OFF
        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 20)
            ->assertSet('discountPercent', 25)
            ->assertSet('totalPrice', 26250.0);
    }

    public function test_shows_sober_notice_when_1_person_away_from_next_tier(): void
    {
        Livewire::test(ReservarSalon::class)
            ->set('attendees_count', 4)
            ->assertSee('Sumando 1 persona más alcanzás el bloque de 5 y obtenés un 10% de descuento grupal');

        Livewire::test(ReservarSalon::class)
            ->set('attendees_count', 9)
            ->assertSee('Sumando 1 persona más alcanzás el bloque de 10 y obtenés un 15% de descuento grupal');
    }

    public function test_detects_conflict_with_own_events_on_same_date(): void
    {
        $conflictDate = now()->addDays(10)->format('Y-m-d');

        Event::create([
            'name'        => 'Recital en Vivo de Prueba',
            'date'        => $conflictDate,
            'time'        => '21:00:00',
            'type'        => 'Recital en Vivo',
            'description' => 'Show íntimo.',
            'capacity'    => 100,
            'price'       => 5000,
            'venue_id'    => 1,
        ]);

        Livewire::test(ReservarSalon::class)
            ->set('date', $conflictDate)
            ->assertSet('dateConflictError', 'El salón ya se encuentra ocupado por un Recital o Evento propio de Sound Heritage en esa fecha. Por favor elegí otro día.')
            ->assertSee('El salón ya se encuentra ocupado');
    }

    public function test_can_submit_and_create_reservation_in_pending_status(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $validDate = now()->addDays(14)->format('Y-m-d');

        Livewire::test(ReservarSalon::class)
            ->set('type', 'Grabación Acústica')
            ->set('attendees_count', 6)
            ->set('date', $validDate)
            ->set('shift', 'Tarde (15:00 a 19:00 hs)')
            ->set('payment_mode', 'seña')
            ->set('payment_method', 'Transferencia Bancaria')
            ->set('first_name', 'Julián')
            ->set('last_name', 'Gómez')
            ->set('document_number', '38999888')
            ->set('email', 'julian.gomez@test.com')
            ->set('phone', '11 5555 4444')
            ->set('notes', 'Traemos amplificador valvular y pedales analógicos')
            ->call('confirmarReserva')
            ->assertSet('reservationConfirmed', true);

        $this->assertDatabaseHas('customers', [
            'document_number' => '38999888',
            'email'           => 'julian.gomez@test.com',
            'first_name'      => 'Julián',
        ]);

        $this->assertDatabaseHas('reservations', [
            'type'            => 'Grabación Acústica',
            'shift'           => 'Tarde (15:00 a 19:00 hs)',
            'attendees_count' => 6,
            'status'          => 'Pendiente',
            'payment_method'  => 'Transferencia Bancaria',
            'payment_status'  => 'Pendiente de Seña',
            'notes'           => 'Traemos amplificador valvular y pedales analógicos',
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ReservationPendingMail::class, function ($mail) {
            return $mail->reservation->customer->email === 'julian.gomez@test.com';
        });
    }

    public function test_updating_reservation_to_confirmed_triggers_confirmation_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $customer = Customer::create([
            'first_name'      => 'Facundo',
            'last_name'       => 'Pérez',
            'document_number' => '30111222',
            'email'           => 'facundo.perez@test.com',
            'phone'           => '11 4444 3333',
            'address'         => 'Av. Corrientes 500',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => 1,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => now()->addDays(20)->format('Y-m-d'),
            'shift'           => 'Noche (20:00 a 24:00 hs)',
            'price'           => 35000,
            'deposit_amount'  => 10500,
            'payment_method'  => 'Transferencia Bancaria',
            'payment_status'  => 'Pendiente de Seña',
            'attendees_count' => 4,
            'status'          => 'Pendiente',
            'customer_id'     => $customer->id,
            'venue_id'        => 1,
        ]);

        // Simular que el administrador o encargado en Filament pasa la reserva a 'Confirmada'
        $reservation->update(['status' => 'Confirmada']);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ReservationConfirmedMail::class, function ($mail) use ($customer) {
            return $mail->reservation->customer->email === $customer->email;
        });
    }

    public function test_can_submit_reservation_with_full_total_payment(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $validDate = now()->addDays(15)->format('Y-m-d');

        Livewire::test(ReservarSalon::class)
            ->set('type', 'Ensayo de Banda')
            ->set('attendees_count', 4)
            ->set('date', $validDate)
            ->set('shift', 'Tarde (15:00 a 19:00 hs)')
            ->set('payment_mode', 'total')
            ->set('payment_method', 'Efectivo en Mostrador')
            ->set('first_name', 'Agustina')
            ->set('last_name', 'López')
            ->set('document_number', '40123456')
            ->set('email', 'agustina.lopez@test.com')
            ->set('phone', '11 6666 7777')
            ->call('confirmarReserva')
            ->assertSet('reservationConfirmed', true);

        $this->assertDatabaseHas('reservations', [
            'type'            => 'Ensayo de Banda',
            'price'           => 35000,
            'deposit_amount'  => 35000,
            'payment_status'  => 'Pendiente de Pago Total',
            'payment_method'  => 'Efectivo en Mostrador',
            'status'          => 'Pendiente',
        ]);
    }

    public function test_reservation_does_not_overwrite_master_customer_profile(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $user = \App\Models\User::factory()->create([
            'name'  => 'Mariana Master',
            'email' => 'mariana.master@test.com',
        ]);

        $customer = \App\Models\Customer::create([
            'user_id'         => $user->id,
            'first_name'      => 'Mariana',
            'last_name'       => 'Master',
            'document_number' => '44123456',
            'email'           => 'mariana.master@test.com',
            'phone'           => '3764887766',
            'address'         => 'Calle Colón 1234',
            'city_id'         => 1,
            'tax_condition'   => 'Consumidor Final',
        ]);

        $validDate = now()->addDays(20)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(ReservarSalon::class)
            ->assertSet('first_name', 'Mariana')
            ->assertSet('last_name', 'Master')
            ->assertSet('document_number', '44123456')
            ->set('first_name', 'MarianaEditada')
            ->set('phone', '99999999')
            ->set('date', $validDate)
            ->set('shift', 'Tarde (15:00 a 19:00 hs)')
            ->set('payment_mode', 'seña')
            ->set('payment_method', 'Efectivo en Mostrador')
            ->call('confirmarReserva')
            ->assertSet('reservationConfirmed', true);

        // El registro maestro en customers DEBE permanecer intacto (sin sobreescribirse)
        $customer->refresh();
        $this->assertEquals('Mariana', $customer->first_name);
        $this->assertEquals('Master', $customer->last_name);
        $this->assertEquals('3764887766', $customer->phone);
    }
}
