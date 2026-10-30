<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\User;
use App\Models\Venue;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MercadoPagoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
    }

    public function test_webhook_successfully_reconciles_invoice_order(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas', 'province_id' => $prov->id]);

        $store = Store::create([
            'company_name'  => 'Sound Heritage Central',
            'tax_id'        => '30112233445',
            'tax_condition' => 'Responsable Inscripto',
            'start_date'    => now()->subYears(2)->toDateString(),
            'address'       => 'Av. Corrientes 1234',
            'phone'         => '1122334455',
            'city_id'       => $city->id,
        ]);

        $cat = EmployeeCategory::firstOrCreate(['name' => 'Ventas']);
        $employee = Employee::create([
            'first_name'           => 'Vendedor',
            'last_name'            => 'Test',
            'document_number'      => '20111222',
            'email'                => 'vendedor@test.com',
            'address'              => 'Av. San Martín 500',
            'phone'                => '1155443322',
            'city_id'              => $city->id,
            'employee_category_id' => $cat->id,
            'store_id'             => $store->id,
        ]);

        $customer = Customer::create([
            'first_name'      => 'Comprador',
            'last_name'       => 'Test',
            'document_number' => '30111222',
            'email'           => 'comprador@test.com',
            'address'         => 'Calle Falsa 123',
            'phone'           => '1199887766',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $invoice = Invoice::create([
            'invoice_type'   => 'B',
            'point_of_sale'  => 1,
            'receipt_number' => 101,
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->toDateString(),
            'total'          => 25000.00,
            'store_id'       => $store->id,
            'customer_id'    => $customer->id,
            'employee_id'    => $employee->id,
            'payment_status' => 'pending',
        ]);

        $payload = [
            'action' => 'order.processed',
            'api_version' => 'v1',
            'data' => [
                'id'                 => 'ORD_TEST_9999',
                'external_reference' => "INV-{$invoice->id}",
                'status'             => 'processed',
                'status_detail'      => 'accredited',
                'transactions'       => [
                    'payments' => [
                        [
                            'id'            => 'PAY_TEST_8888',
                            'amount'        => '25000.00',
                            'status'        => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ],
            'type' => 'order',
        ];

        $response = $this->postJson('/api/mercadopago/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $invoice->refresh();
        $this->assertEquals('completed', $invoice->payment_status);
        $this->assertEquals('ORD_TEST_9999', $invoice->mp_order_id);
        $this->assertEquals('PAY_TEST_8888', $invoice->mp_payment_id);
        $paymentMethod = \App\Models\PaymentMethod::where('name', 'Mercado Pago')->first();
        $this->assertDatabaseHas('payment_details', [
            'invoice_id'        => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
        ]);
    }

    public function test_webhook_successfully_reconciles_reservation_order(): void
    {
        $venue = Venue::create([
            'name'        => 'Auditorio Sound Heritage',
            'capacity'    => 150,
            'hourly_rate' => 15000.00,
            'location'    => 'Av. Corrientes 1234',
        ]);

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas', 'province_id' => $prov->id]);

        $customer = Customer::create([
            'first_name'      => 'Organizador',
            'last_name'       => 'Test',
            'document_number' => '30444555',
            'email'           => 'organizador@test.com',
            'address'         => 'Calle Eventos 456',
            'phone'           => '1133445566',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Ensayo',
            'date'            => now()->addDays(7)->toDateString(),
            'status'          => 'Pendiente',
            'price'           => 45000.00,
            'deposit_amount'  => 15000.00,
            'payment_status'  => 'pending',
            'attendees_count' => 5,
            'shift'           => 'Tarde (14:00 - 18:00)',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
        ]);

        $payload = [
            'action' => 'order.processed',
            'api_version' => 'v1',
            'data' => [
                'id'                 => 'ORD_RES_7777',
                'external_reference' => "RES-{$reservation->id}",
                'status'             => 'processed',
                'status_detail'      => 'accredited',
                'transactions'       => [
                    'payments' => [
                        [
                            'id'            => 'PAY_RES_6666',
                            'amount'        => '15000.00',
                            'status'        => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ],
            'type' => 'order',
        ];

        $response = $this->postJson('/api/mercadopago/webhook', $payload);

        $response->assertStatus(200);

        $reservation->refresh();
        $this->assertEquals('Pendiente', $reservation->status);
        $this->assertEquals('Señada (Resta 70%)', $reservation->payment_status);
        $this->assertEquals('ORD_RES_7777', $reservation->mp_order_id);
        $this->assertEquals('PAY_RES_6666', $reservation->mp_payment_id);
    }

    public function test_mercadopago_service_create_qr_order_payload(): void
    {
        Http::fake([
            'https://api.mercadopago.com/v1/orders' => Http::response([
                'id'                 => 'ORD_MOCK_1234',
                'status'             => 'created',
                'external_reference' => 'INV-99',
            ], 200),
        ]);

        $service = new MercadoPagoService();
        $result = $service->createQrOrder(
            amount: 50000.00,
            description: 'Guitarra Fender Stratocaster',
            externalReference: 'INV-99',
            posId: 'CAJA_1'
        );

        $this->assertNotNull($result);
        $this->assertEquals('ORD_MOCK_1234', $result['id']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://api.mercadopago.com/v1/orders'
                && $data['type'] === 'qr'
                && $data['config']['qr']['external_pos_id'] === 'CAJA_1'
                && $data['config']['qr']['mode'] === 'static'
                && $data['total_amount'] == 50000.00;
        });
    }

    public function test_mercadopago_service_create_preference_payload(): void
    {
        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id'                 => 'PREF_MOCK_5678',
                'init_point'         => 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREF_MOCK_5678',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREF_MOCK_5678',
            ], 200),
        ]);

        $service = new MercadoPagoService();
        $result = $service->createCheckoutPreference(
            items: [
                ['title' => 'Cuerdas DAddario', 'unit_price' => 12000.00, 'quantity' => 2]
            ],
            payer: [
                'name'    => 'Juan',
                'surname' => 'Perez',
                'email'   => 'juan@example.com',
            ],
            externalReference: 'INV-100'
        );

        $this->assertNotNull($result);
        $this->assertEquals('PREF_MOCK_5678', $result['id']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://api.mercadopago.com/checkout/preferences'
                && $data['auto_return'] === 'approved'
                && $data['items'][0]['title'] === 'Cuerdas DAddario'
                && $data['items'][0]['unit_price'] == 12000.00;
        });
    }

    public function test_cancelling_invoice_triggers_mercadopago_refund(): void
    {
        Http::fake([
            'https://api.mercadopago.com/v1/payments/PAY_12345/refunds' => Http::response(['status' => 'approved', 'id' => 999], 200),
        ]);

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas', 'province_id' => $prov->id]);

        $store = Store::create([
            'company_name'  => 'Sound Heritage Central',
            'tax_id'        => '30112233445',
            'tax_condition' => 'Responsable Inscripto',
            'start_date'    => now()->subYears(2)->toDateString(),
            'address'       => 'Av. Corrientes 1234',
            'phone'         => '1122334455',
            'city_id'       => $city->id,
        ]);

        $cat = EmployeeCategory::firstOrCreate(['name' => 'Ventas']);
        $employee = Employee::create([
            'first_name'           => 'Vendedor',
            'last_name'            => 'Test',
            'document_number'      => '20111222',
            'email'                => 'vendedor@test.com',
            'address'              => 'Av. San Martín 500',
            'phone'                => '1155443322',
            'city_id'              => $city->id,
            'employee_category_id' => $cat->id,
            'store_id'             => $store->id,
        ]);

        $customer = Customer::create([
            'first_name'      => 'Comprador',
            'last_name'       => 'Test',
            'document_number' => '30111222',
            'email'           => 'comprador@test.com',
            'address'         => 'Calle Falsa 123',
            'phone'           => '1199887766',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $invoice = Invoice::create([
            'invoice_type'   => 'B',
            'point_of_sale'  => 1,
            'receipt_number' => 202,
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->toDateString(),
            'total'          => 45000.00,
            'store_id'       => $store->id,
            'customer_id'    => $customer->id,
            'employee_id'    => $employee->id,
            'payment_status' => 'completed',
            'mp_payment_id'  => 'PAY_12345',
        ]);

        // Anular factura (soft delete)
        $invoice->delete();

        // Verificar que se haya enviado la petición de reembolso a la API de Mercado Pago
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.mercadopago.com/v1/payments/PAY_12345/refunds'
                && $request->method() === 'POST';
        });
    }

    public function test_check_pending_qr_orders_polling_reconciles_invoice(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas', 'province_id' => $prov->id]);

        $store = Store::create([
            'company_name'  => 'Sound Heritage Central',
            'tax_id'        => '30112233445',
            'tax_condition' => 'Responsable Inscripto',
            'start_date'    => now()->subYears(2)->toDateString(),
            'address'       => 'Av. Corrientes 1234',
            'phone'         => '1122334455',
            'city_id'       => $city->id,
        ]);

        $cat = EmployeeCategory::firstOrCreate(['name' => 'Ventas']);
        $employee = Employee::create([
            'first_name'           => 'Vendedor',
            'last_name'            => 'Test',
            'document_number'      => '20111222',
            'email'                => 'vendedor@test.com',
            'address'              => 'Av. San Martín 500',
            'phone'                => '1155443322',
            'city_id'              => $city->id,
            'employee_category_id' => $cat->id,
            'store_id'             => $store->id,
        ]);

        $customer = Customer::create([
            'first_name'      => 'Comprador',
            'last_name'       => 'Test',
            'document_number' => '30111222',
            'email'           => 'comprador@test.com',
            'address'         => 'Calle Falsa 123',
            'phone'           => '1199887766',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $invoice = Invoice::create([
            'invoice_type'   => 'B',
            'point_of_sale'  => 1,
            'receipt_number' => 303,
            'issue_date'     => now()->toDateString(),
            'due_date'       => now()->toDateString(),
            'total'          => 18500.00,
            'store_id'       => $store->id,
            'customer_id'    => $customer->id,
            'employee_id'    => $employee->id,
            'payment_status' => 'pending',
            'mp_order_id'    => 'ORD_POLL_TEST_123',
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/orders/*' => Http::response([
                'id' => 'ORD_POLL_TEST_123',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'total_amount' => '18500.00',
                'total_paid_amount' => '18500.00',
                'transactions' => [
                    'payments' => [
                        [
                            'id' => 'PAY_POLL_1',
                            'reference_id' => '184957603579',
                            'status' => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $page = new \App\Filament\Admin\Resources\InvoiceResource\Pages\ListInvoices();
        $page->checkPendingQrOrders();

        $invoice->refresh();

        $this->assertEquals('completed', $invoice->payment_status);
        $this->assertEquals('184957603579', $invoice->mp_payment_id);
    }

    public function test_cancelling_reservation_triggers_mercadopago_refund(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Titular',
            'last_name'       => 'Reserva',
            'document_number' => '40111222',
            'email'           => 'titular@test.com',
            'address'         => 'Calle 100',
            'phone'           => '1133221100',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => now()->addDays(10)->toDateString(),
            'shift'           => 'Tarde (15:00 a 19:00 hs)',
            'price'           => 35000.00,
            'deposit_amount'  => 10500.00,
            'payment_method'  => 'Mercado Pago',
            'payment_status'  => 'completed',
            'status'          => 'Confirmada',
            'mp_payment_id'   => 'PAY_RES_9999',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
            'attendees_count' => 4,
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/payments/PAY_RES_9999/refunds' => Http::response(['status' => 'approved'], 201),
        ]);

        // Cancelar reserva
        $reservation->update(['status' => 'Cancelada']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.mercadopago.com/v1/payments/PAY_RES_9999/refunds'
                && $request->method() === 'POST';
        });
    }

    public function test_check_pending_qr_reservations_polling_reconciles_reservation(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Cliente',
            'last_name'       => 'QR Reserva',
            'document_number' => '41111222',
            'email'           => 'clienteqr@test.com',
            'address'         => 'Calle 200',
            'phone'           => '1133221199',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => now()->addDays(5)->toDateString(),
            'shift'           => 'Tarde (15:00 a 19:00 hs)',
            'price'           => 35000.00,
            'deposit_amount'  => 10500.00,
            'payment_method'  => 'Mercado Pago',
            'payment_status'  => 'pending',
            'status'          => 'Pendiente',
            'mp_order_id'     => 'ORD_RES_POLL_555',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
            'attendees_count' => 4,
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/orders/*' => Http::response([
                'id' => 'ORD_RES_POLL_555',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'total_amount' => '10500.00',
                'total_paid_amount' => '10500.00',
                'transactions' => [
                    'payments' => [
                        [
                            'id' => 'PAY_RES_POLL_1',
                            'reference_id' => '184999888777',
                            'status' => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $page = new \App\Filament\Admin\Resources\ReservationResource\Pages\ListReservations();
        $page->checkPendingQrOrders();

        $reservation->refresh();

        $this->assertEquals('Señada (Resta 70%)', $reservation->payment_status);
        $this->assertEquals('Pendiente', $reservation->status);
        $this->assertEquals('184999888777', $reservation->mp_payment_id);
    }

    public function test_cancelling_attendance_triggers_mercadopago_refund(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);

        $customer = Customer::create([
            'first_name' => 'Carla',
            'last_name' => 'Gomez',
            'email' => 'carla.gomez@test.com',
            'document_type' => 'DNI',
            'document_number' => '33999888',
            'phone' => '3764123456',
            'address' => 'Av. Corrientes 100',
            'city_id' => $city->id,
        ]);

        $venue = Venue::create([
            'name' => 'Sala Principal',
            'location' => 'Planta Baja',
            'capacity' => 100,
        ]);

        $event = Event::create([
            'name' => 'Concierto Barroco',
            'type' => 'Concierto',
            'date' => now()->addDays(5)->toDateString(),
            'capacity' => 50,
            'price' => 5000.00,
            'venue_id' => $venue->id,
        ]);

        $attendance = Attendance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'registration_date' => now()->toDateString(),
            'price' => 5000.00,
            'payment_method' => 'Mercado Pago',
            'payment_status' => 'completed',
            'mp_payment_id' => '184999444333',
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/payments/184999444333/refunds' => Http::response([
                'id' => 99887766,
                'payment_id' => 184999444333,
                'amount' => 5000.00,
                'status' => 'approved',
            ], 201),
        ]);

        $attendance->delete();

        $this->assertSoftDeleted('attendances', ['id' => $attendance->id]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/payments/184999444333/refunds');
        });
    }

    public function test_check_pending_qr_attendances_polling_reconciles_attendance(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);

        $customer = Customer::create([
            'first_name' => 'Martin',
            'last_name' => 'Rios',
            'email' => 'martin.rios@test.com',
            'document_type' => 'DNI',
            'document_number' => '32888777',
            'phone' => '3764987654',
            'address' => 'San Martin 500',
            'city_id' => $city->id,
        ]);

        $venue = Venue::create([
            'name' => 'Auditorio B',
            'location' => 'Planta Alta',
            'capacity' => 80,
        ]);

        $event = Event::create([
            'name' => 'Recital Acústico',
            'type' => 'Recital',
            'date' => now()->addDays(7)->toDateString(),
            'capacity' => 40,
            'price' => 4000.00,
            'venue_id' => $venue->id,
        ]);

        $attendance = Attendance::create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'registration_date' => now()->toDateString(),
            'price' => 4000.00,
            'payment_method' => 'Mercado Pago',
            'payment_status' => 'pending',
            'mp_order_id' => 'ORD_ATT_POLL_777',
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/orders/ORD_ATT_POLL_777' => Http::response([
                'id' => 'ORD_ATT_POLL_777',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'total_amount' => '4000.00',
                'total_paid_amount' => '4000.00',
                'transactions' => [
                    'payments' => [
                        [
                            'id' => 'PAY_ATT_POLL_1',
                            'reference_id' => '184999111222',
                            'status' => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $page = new \App\Filament\Admin\Resources\AttendanceResource\Pages\ListAttendances();
        $page->checkPendingQrOrders();

        $attendance->refresh();

        $this->assertEquals('completed', $attendance->payment_status);
        $this->assertEquals('184999111222', $attendance->mp_payment_id);
    }

    public function test_product_pricing_accessors_option_one(): void
    {
        // 1. Producto con 20% de oferta sobre lista de $100.000
        $productWithOffer = new Product([
            'name'                => 'Guitarra Eléctrica Custom',
            'price'               => 100000.00,
            'discount_percentage' => 20,
            'type'                => 'cuerda',
            'stock'               => 5,
        ]);

        $this->assertEquals(20000.00, $productWithOffer->offer_savings);
        $this->assertEquals(80000.00, $productWithOffer->offer_price); // Para Mercado Pago
        $this->assertEquals(72000.00, $productWithOffer->cash_price);  // 10% adicional sobre $80.000
        $this->assertEquals(28000.00, $productWithOffer->total_cash_savings);

        // 2. Producto sin oferta sobre lista de $100.000
        $productNoOffer = new Product([
            'name'                => 'Bajo Eléctrico',
            'price'               => 100000.00,
            'discount_percentage' => 0,
            'type'                => 'cuerda',
            'stock'               => 3,
        ]);

        $this->assertEquals(0.00, $productNoOffer->offer_savings);
        $this->assertEquals(100000.00, $productNoOffer->offer_price);
        $this->assertEquals(90000.00, $productNoOffer->cash_price);  // 10% de contado
        $this->assertEquals(10000.00, $productNoOffer->total_cash_savings);
    }

    public function test_webhook_successfully_reconciles_reservation_total_payment(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Banda',
            'last_name'       => 'Rock',
            'document_number' => '30999888',
            'email'           => 'banda@rock.com',
            'address'         => 'Av. Siempre Viva 123',
            'phone'           => '1155667788',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => now()->addDays(5)->toDateString(),
            'status'          => 'Pendiente',
            'price'           => 50000.00,
            'deposit_amount'  => 15000.00,
            'payment_status'  => 'Señada (Resta 70%)',
            'attendees_count' => 4,
            'shift'           => 'Tarde (15:00 a 19:00 hs)',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
        ]);

        $payload = [
            'action' => 'order.processed',
            'api_version' => 'v1',
            'data' => [
                'id'                 => 'ORD_RES_TOTAL_999',
                'external_reference' => "RES-{$reservation->id}-TOTAL",
                'status'             => 'processed',
                'status_detail'      => 'accredited',
                'transactions'       => [
                    'payments' => [
                        [
                            'id'            => 'PAY_RES_TOTAL_888',
                            'amount'        => '35000.00',
                            'status'        => 'processed',
                            'status_detail' => 'accredited',
                        ]
                    ]
                ]
            ],
            'type' => 'order',
        ];

        $response = $this->postJson('/api/mercadopago/webhook', $payload);
        $response->assertStatus(200);

        $reservation->refresh();
        $this->assertEquals('Totalmente Abonada', $reservation->payment_status);
        $this->assertEquals('Pendiente', $reservation->status); // Sigue en Pendiente hasta aprobación manual
    }

    public function test_manual_approval_of_reservation_triggers_confirmation_mail(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Solista',
            'last_name'       => 'Piano',
            'document_number' => '32111222',
            'email'           => 'solista@piano.com',
            'address'         => 'Av. Alvear 456',
            'phone'           => '1177889900',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $reservation = Reservation::create([
            'type'            => 'Grabación Acústica',
            'date'            => now()->addDays(8)->toDateString(),
            'status'          => 'Pendiente',
            'price'           => 70000.00,
            'deposit_amount'  => 21000.00,
            'payment_status'  => 'Señada (Resta 70%)',
            'attendees_count' => 2,
            'shift'           => 'Mañana (10:00 a 14:00 hs)',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
        ]);

        // Aprobación manual del encargado
        $reservation->update(['status' => 'Confirmada']);

        Mail::assertSent(\App\Mail\ReservationConfirmedMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_creating_reservation_in_erp_dispatches_pending_mail_to_customer(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Martín',
            'last_name'       => 'Pérez',
            'document_number' => '33444555',
            'email'           => 'martin.perez@test.com',
            'address'         => 'Calle Falsa 123',
            'phone'           => '1122334455',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $adminUser = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminUser->assignRole($adminRole);
        $this->actingAs($adminUser);

        Livewire::test(\App\Filament\Admin\Resources\ReservationResource\Pages\CreateReservation::class)
            ->fillForm([
                'customer_id'     => $customer->id,
                'venue_id'        => $venue->id,
                'type'            => 'Ensayo de Banda',
                'attendees_count' => 4,
                'date'            => now()->addDays(12)->toDateString(),
                'shift'           => 'Tarde (15:00 a 19:00 hs)',
                'payment_method'  => 'Efectivo en Mostrador',
                'payment_mode'    => 'seña',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertSent(\App\Mail\ReservationPendingMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_creating_attendance_in_erp_dispatches_event_tickets_mail_to_customer(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $event = Event::create([
            'name'        => 'Show de Jazz en Vivo',
            'date'        => now()->addDays(5)->toDateString(),
            'time'        => '21:00:00',
            'type'        => 'Recital en Vivo',
            'description' => 'Show acústico de jazz.',
            'capacity'    => 50,
            'price'       => 8000,
            'venue_id'    => $venue->id,
        ]);

        $customer = Customer::create([
            'first_name'      => 'Clara',
            'last_name'       => 'Vázquez',
            'document_number' => '36777888',
            'email'           => 'clara.vazquez@test.com',
            'address'         => 'San Martín 500',
            'phone'           => '1188990011',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $adminUser = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminUser->assignRole($adminRole);
        $this->actingAs($adminUser);

        Livewire::test(\App\Filament\Admin\Resources\AttendanceResource\Pages\CreateAttendance::class)
            ->fillForm([
                'event_id'       => $event->id,
                'customer_id'    => $customer->id,
                'quantity'       => 2,
                'payment_method' => 'Efectivo',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertSent(\App\Mail\EventTicketsMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_creating_reservation_in_erp_with_abonar_total_marks_fully_paid(): void
    {
        Mail::fake();

        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Rodrigo',
            'last_name'       => 'Molina',
            'document_number' => '39888777',
            'email'           => 'rodrigo.molina@test.com',
            'address'         => 'Belgrano 123',
            'phone'           => '1199887766',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $adminUser = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminUser->assignRole($adminRole);
        $this->actingAs($adminUser);

        $testDate = now()->addDays(25)->toDateString();

        Livewire::test(\App\Filament\Admin\Resources\ReservationResource\Pages\CreateReservation::class)
            ->fillForm([
                'customer_id'     => $customer->id,
                'venue_id'        => $venue->id,
                'type'            => 'Ensayo de Banda',
                'attendees_count' => 4,
                'date'            => $testDate,
                'shift'           => 'Mañana (10:00 a 14:00 hs)',
                'payment_method'  => 'Efectivo en Mostrador',
                'payment_mode'    => 'total',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = Reservation::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($created);
        $this->assertEquals('Totalmente Abonada', $created->payment_status);
        $this->assertEquals((float) $created->price, (float) $created->deposit_amount);
    }

    public function test_cannot_create_duplicate_reservation_on_same_date_and_shift(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Esteban',
            'last_name'       => 'Quito',
            'document_number' => '31222333',
            'email'           => 'esteban.quito@test.com',
            'address'         => 'Rivadavia 456',
            'phone'           => '1133221100',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $testDate = now()->addDays(30)->toDateString();
        $shift = 'Tarde (15:00 a 19:00 hs)';

        // Primera reserva existente (estado Pendiente)
        Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => $testDate,
            'shift'           => $shift,
            'price'           => 35000,
            'deposit_amount'  => 10500,
            'payment_method'  => 'Efectivo en Mostrador',
            'payment_status'  => 'Señada (Resta 70%)',
            'attendees_count' => 4,
            'status'          => 'Pendiente',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
        ]);

        $adminUser = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminUser->assignRole($adminRole);
        $this->actingAs($adminUser);

        // Intentar crear otra reserva en la misma fecha y mismo turno
        Livewire::test(\App\Filament\Admin\Resources\ReservationResource\Pages\CreateReservation::class)
            ->fillForm([
                'customer_id'     => $customer->id,
                'venue_id'        => $venue->id,
                'type'            => 'Grabación Acústica',
                'attendees_count' => 5,
                'date'            => $testDate,
                'shift'           => $shift,
                'payment_method'  => 'Efectivo en Mostrador',
                'payment_mode'    => 'seña',
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);
    }

    public function test_cannot_create_event_on_date_with_active_reservation(): void
    {
        $prov = \App\Models\Province::firstOrCreate(['name' => 'Misiones']);
        $city = \App\Models\City::firstOrCreate(['name' => 'Posadas'], ['province_id' => $prov->id]);
        $venue = Venue::firstOrCreate(['name' => 'Auditorio Sound Heritage'], [
            'capacity' => 150,
            'hourly_rate' => 15000.00,
            'location' => 'Av. Corrientes 1234',
        ]);

        $customer = Customer::create([
            'first_name'      => 'Romina',
            'last_name'       => 'Díaz',
            'document_number' => '32444111',
            'email'           => 'romina.diaz@test.com',
            'address'         => 'Mitre 789',
            'phone'           => '1144556677',
            'tax_condition'   => 'Consumidor Final',
            'city_id'         => $city->id,
        ]);

        $testDate = now()->addDays(35)->toDateString();

        // Reserva activa/pendiente
        Reservation::create([
            'type'            => 'Ensayo de Banda',
            'date'            => $testDate,
            'shift'           => 'Noche (20:00 a 24:00 hs)',
            'price'           => 35000,
            'deposit_amount'  => 10500,
            'payment_method'  => 'Efectivo en Mostrador',
            'payment_status'  => 'Señada (Resta 70%)',
            'attendees_count' => 4,
            'status'          => 'Pendiente',
            'customer_id'     => $customer->id,
            'venue_id'        => $venue->id,
        ]);

        $adminUser = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminUser->assignRole($adminRole);
        $this->actingAs($adminUser);

        // Intentar crear un Evento propio en esa misma fecha
        Livewire::test(\App\Filament\Admin\Resources\EventResource\Pages\CreateEvent::class)
            ->fillForm([
                'name'        => 'Festival de Blues',
                'type'        => 'Recital',
                'venue_id'    => $venue->id,
                'date'        => $testDate,
                'time'        => '21:00',
                'price'       => 5000,
                'capacity'    => 100,
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);
    }
}
