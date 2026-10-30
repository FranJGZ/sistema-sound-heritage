<?php

namespace Tests\Feature;

use App\Livewire\CheckoutTienda;
use App\Mail\EventTicketsMail;
use App\Mail\PurchaseConfirmationMail;
use App\Models\City;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTiendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Province::firstOrCreate(['id' => 1], ['name' => 'Buenos Aires']);
        City::firstOrCreate(['id' => 1], ['name' => 'CABA', 'province_id' => 1]);
        Venue::firstOrCreate(['id' => 1], ['name' => 'Auditorio Sound Heritage', 'location' => 'Av. Corrientes 1234, CABA', 'capacity' => 150]);
        
        Store::firstOrCreate(
            ['id' => 1],
            [
                'company_name'  => 'Sound Heritage S.R.L.',
                'address'       => 'Av. Corrientes 1234',
                'tax_condition' => 'Responsable Inscripto',
                'phone'         => '1143219876',
                'tax_id'        => '30712345679',
                'start_date'    => '2020-03-01',
            ]
        );

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);

        $cat = \App\Models\EmployeeCategory::firstOrCreate(['id' => 1], ['name' => 'Ventas']);
        Employee::firstOrCreate(
            ['id' => 1],
            [
                'first_name'           => 'Carlos',
                'last_name'            => 'Admin',
                'document_number'      => '20123456',
                'phone'                => '1143219876',
                'address'              => 'Av. Corrientes 1234',
                'city_id'              => 1,
                'employee_category_id' => $cat->id,
            ]
        );
    }

    public function test_checkout_page_renders_with_empty_state_when_no_cart(): void
    {
        $response = $this->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('checkout-tienda');
        $response->assertSee('Tu carrito está vacío');
    }

    public function test_checkout_displays_cart_items_and_financial_breakdown(): void
    {
        $product = Product::create([
            'type'                => 'Guitarra Eléctrica',
            'brand'               => 'Fender',
            'model'               => 'AV 57',
            'name'                => 'Fender Stratocaster American Vintage',
            'price'               => 1500000.0,
            'discount_percentage' => 10,
            'stock'               => 5,
        ]);

        $cart = [
            $product->id => [
                'type'                => 'product',
                'name'                => $product->name,
                'price'               => 1500000.0,
                'discount_percentage' => 10,
                'cantidad'            => 1,
                'image'               => null,
            ],
        ];

        session()->put('carrito', $cart);

        Livewire::test(CheckoutTienda::class)
            ->assertSet('hasPhysicalProducts', true)
            ->assertSet('cartCount', 1)
            ->assertSet('cartListSubtotal', 1500000.0)
            ->assertSet('cartSavings', 150000.0)
            ->assertSet('cartSubtotal', 1350000.0)
            ->assertSee('Fender Stratocaster American Vintage')
            ->assertSee('Total a Pagar')
            ->assertSee('1.350.000');
    }

    public function test_can_complete_purchase_with_physical_products_and_receives_invoice_email(): void
    {
        Mail::fake();

        $product = Product::create([
            'type'                => 'Guitarra Eléctrica',
            'brand'               => 'Gibson',
            'model'               => 'Standard 50s',
            'name'                => 'Gibson Les Paul Standard 50s',
            'price'               => 2000000.0,
            'discount_percentage' => 0,
            'stock'               => 3,
        ]);

        $cart = [
            $product->id => [
                'type'                => 'product',
                'name'                => $product->name,
                'price'               => 2000000.0,
                'discount_percentage' => 0,
                'cantidad'            => 1,
            ],
        ];

        session()->put('carrito', $cart);

        Livewire::test(CheckoutTienda::class)
            ->set('first_name', 'Esteban')
            ->set('last_name', 'Maldonado')
            ->set('document_number', '32123456')
            ->set('email', 'esteban.maldonado@test.com')
            ->set('phone', '11 9876 5432')
            ->set('delivery_method', 'Retiro en Local Sound Heritage')
            ->set('payment_method', 'Transferencia Bancaria')
            ->call('confirmarCompra')
            ->assertSet('orderConfirmed', true)
            ->assertSee('Compra Finalizada con Éxito');

        $this->assertDatabaseHas('customers', [
            'document_number' => '32123456',
            'email'           => 'esteban.maldonado@test.com',
            'first_name'      => 'Esteban',
        ]);

        // Con Transferencia Bancaria (pago contado) aplica el 10% de descuento ($1.800.000)
        $this->assertDatabaseHas('invoices', [
            'total' => 1800000.0,
        ]);

        // Stock del producto debe haberse decrementado
        $this->assertEquals(2, $product->fresh()->stock);

        // El carrito en sesión debe estar limpio
        $this->assertEmpty(session()->get('carrito'));

        Mail::assertSent(PurchaseConfirmationMail::class, function ($mail) {
            return $mail->invoice->customer->email === 'esteban.maldonado@test.com';
        });
    }

    public function test_can_complete_purchase_with_events_only_and_creates_attendances_and_event_ticket_email(): void
    {
        Mail::fake();

        $event = Event::create([
            'name'        => 'Masterclass de Mezcla Analógica',
            'date'        => now()->addDays(10)->format('Y-m-d'),
            'time'        => '19:00:00',
            'type'        => 'Masterclass / Taller',
            'description' => 'Taller de producción.',
            'capacity'    => 50,
            'price'       => 15000.0,
            'venue_id'    => 1,
        ]);

        $cart = [
            'event_' . $event->id => [
                'type'     => 'event',
                'event_id' => $event->id,
                'name'     => 'Entrada: ' . $event->name,
                'price'    => 15000.0,
                'cantidad' => 2,
            ],
        ];

        session()->put('carrito', $cart);

        Livewire::test(CheckoutTienda::class)
            ->assertSet('hasPhysicalProducts', false)
            ->assertSet('hasEvents', true)
            ->set('first_name', 'Lucía')
            ->set('last_name', 'Navarro')
            ->set('document_number', '35987654')
            ->set('email', 'lucia.navarro@test.com')
            ->set('phone', '11 3333 7777')
            ->set('payment_method', 'Mercado Pago / Tarjetas')
            ->call('confirmarCompra')
            ->assertSet('orderConfirmed', true);

        // Debe haber registrado 2 attendances
        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
        ]);

        Mail::assertSent(EventTicketsMail::class, function ($mail) {
            return $mail->customer->email === 'lucia.navarro@test.com'
                && count($mail->attendances) === 2;
        });
    }

    public function test_checkout_does_not_overwrite_master_customer_profile(): void
    {
        Mail::fake();

        $user = \App\Models\User::factory()->create([
            'name'  => 'Facundo Master',
            'email' => 'facundo.master@test.com',
        ]);

        $customer = Customer::create([
            'user_id'         => $user->id,
            'first_name'      => 'Facundo',
            'last_name'       => 'Master',
            'document_number' => '40123456',
            'email'           => 'facundo.master@test.com',
            'phone'           => '3764998877',
            'address'         => 'Calle Colón 1234',
            'city_id'         => 1,
            'tax_condition'   => 'Consumidor Final',
        ]);

        $product = Product::create([
            'type'                => 'Accesorio',
            'brand'               => 'Dunlop',
            'name'                => 'Púas Tortex 0.88',
            'price'               => 15000.0,
            'discount_percentage' => 0,
            'stock'               => 10,
        ]);

        session()->put('carrito', [
            $product->id => [
                'type'     => 'product',
                'name'     => $product->name,
                'price'    => 15000.0,
                'cantidad' => 1,
            ],
        ]);

        $this->actingAs($user);

        Livewire::test(CheckoutTienda::class)
            ->assertSet('first_name', 'Facundo')
            ->assertSet('last_name', 'Master')
            ->assertSet('document_number', '40123456')
            ->set('first_name', 'NombreTemporal')
            ->set('phone', '11111111')
            ->set('delivery_method', 'Retiro en Local Sound Heritage')
            ->set('payment_method', 'Transferencia Bancaria')
            ->call('confirmarCompra')
            ->assertSet('orderConfirmed', true);

        // El registro maestro en customers DEBE permanecer intacto (sin sobreescribirse)
        $customer->refresh();
        $this->assertEquals('Facundo', $customer->first_name);
        $this->assertEquals('Master', $customer->last_name);
        $this->assertEquals('3764998877', $customer->phone);
    }
}
