<?php

namespace Tests\Feature;

use App\Console\Commands\CancelExpiredQrOrders;
use App\Mail\PurchaseConfirmationMail;
use App\Mail\WelcomeUserMail;
use App\Models\City;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class NewRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $province = Province::create(['name' => 'Misiones']);
        City::create(['name' => 'Posadas', 'province_id' => $province->id]);
        Store::create([
            'company_name'  => 'Sound Heritage Central',
            'tax_id'        => '30112233445',
            'tax_condition' => 'Responsable Inscripto',
            'start_date'    => now()->subYears(2)->toDateString(),
            'name'          => 'Sede Central Posadas',
            'address'       => 'Calle Colón 1234',
            'phone'         => '3764123456',
            'city_id'       => 1,
        ]);
        PaymentMethod::create(['name' => 'Efectivo']);
        PaymentMethod::create(['name' => 'Transferencia Bancaria']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);

        $cat = \App\Models\EmployeeCategory::firstOrCreate(['name' => 'Ventas']);
        \App\Models\Employee::create([
            'first_name'           => 'Vendedor',
            'last_name'            => 'Test',
            'document_number'      => '20111222',
            'email'                => 'vendedor@test.com',
            'address'              => 'Av. San Martín 500',
            'phone'                => '1155443322',
            'city_id'              => 1,
            'employee_category_id' => $cat->id,
            'store_id'             => 1,
        ]);
    }

    public function test_guest_purchase_dispatches_confirmation_email_to_entered_email(): void
    {
        Mail::fake();

        $product = Product::create([
            'name'  => 'Guitarra Fender Stratocaster',
            'type'  => 'cuerda',
            'stock' => 10,
            'price' => 50000.00,
        ]);

        session()->put('carrito', [
            $product->id => [
                'id'                  => $product->id,
                'name'                => $product->name,
                'price'               => 50000,
                'cantidad'            => 1,
                'discount_percentage' => 0,
                'type'                => 'product',
            ],
        ]);

        Livewire::test(\App\Livewire\CheckoutTienda::class)
            ->set('first_name', 'Carlos')
            ->set('last_name', 'Invitado')
            ->set('document_number', '32123456')
            ->set('email', 'carlos.invitado@test.com')
            ->set('phone', '3764998877')
            ->set('delivery_method', 'Retiro en Local Sound Heritage')
            ->set('payment_method', 'Efectivo / Transferencia')
            ->call('confirmarCompra')
            ->assertHasNoErrors();

        Mail::assertSent(PurchaseConfirmationMail::class, function ($mail) {
            return $mail->hasTo('carlos.invitado@test.com');
        });
    }

    public function test_retroactive_purchase_linking_when_guest_later_registers(): void
    {
        Mail::fake();

        // 1. Compra de invitado
        $guestCustomer = Customer::create([
            'first_name'      => 'Lucia',
            'last_name'       => 'Perez',
            'document_number' => '35987654',
            'email'           => 'lucia@test.com',
            'phone'           => '3764556677',
            'address'         => 'Av. Corrientes 100',
            'city_id'         => 1,
            'tax_condition'   => 'Consumidor Final',
        ]);

        $invoice = Invoice::create([
            'invoice_type'   => 'B',
            'point_of_sale'  => '0001',
            'receipt_number' => '00000050',
            'issue_date'     => now(),
            'due_date'       => now()->addDays(15),
            'total'          => 10000,
            'store_id'       => 1,
            'customer_id'    => $guestCustomer->id,
            'employee_id'    => 1,
            'payment_status' => 'completed',
        ]);

        $this->assertNull($guestCustomer->user_id);

        // 2. Lucia se registra más tarde con el mismo DNI y correo
        $response = $this->post('/register', [
            'name'                  => 'Lucia Perez',
            'email'                 => 'lucia@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'document_number'       => '35987654',
            'phone'                 => '3764556677',
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));

        $user = User::where('email', 'lucia@test.com')->first();
        $this->assertNotNull($user);

        // Verifica que la ficha previa de Customer quedó vinculada al nuevo User
        $guestCustomer->refresh();
        $this->assertEquals($user->id, $guestCustomer->user_id);

        // Verifica que la factura previa pertenece a la cuenta de la usuaria
        $this->assertCount(1, $user->customer->invoices);
        $this->assertEquals($invoice->id, $user->customer->invoices->first()->id);

        // Verifica que se envió el correo de bienvenida
        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('lucia@test.com');
        });
    }

    public function test_cannot_register_with_dni_claimed_by_another_registered_user(): void
    {
        $existingUser = User::create([
            'name'     => 'Juan Existente',
            'email'    => 'juan@test.com',
            'password' => bcrypt('Password123!'),
        ]);
        $existingUser->assignRole('Cliente');

        Customer::create([
            'user_id'         => $existingUser->id,
            'first_name'      => 'Juan',
            'last_name'       => 'Existente',
            'document_number' => '30111222',
            'email'           => 'juan@test.com',
            'phone'           => '3764112233',
            'address'         => 'Calle 1',
            'city_id'         => 1,
        ]);

        // Intento de registrar otra cuenta con el mismo DNI
        $response = $this->post('/register', [
            'name'                  => 'Otro Usuario',
            'email'                 => 'otro@test.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'document_number'       => '30111222',
        ]);

        $response->assertSessionHasErrors(['document_number']);
    }

    public function test_creating_customer_in_erp_creates_user_and_sends_welcome_mail(): void
    {
        Mail::fake();

        $customer = new Customer([
            'first_name'      => 'Roberto',
            'last_name'       => 'Mostrador',
            'document_number' => '28999888',
            'email'           => 'roberto@presencial.com',
            'phone'           => '3764001122',
            'address'         => 'Av. San Martín 500',
            'city_id'         => 1,
            'tax_condition'   => 'Consumidor Final',
        ]);
        $customer->create_web_account = true;
        $customer->save();

        $this->assertNotNull($customer->user_id);
        $user = User::find($customer->user_id);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Cliente'));

        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('roberto@presencial.com') && $mail->origin === 'presencial';
        });
    }

    public function test_cancel_expired_qr_orders_command_soft_deletes_and_restores_stock(): void
    {
        $product = Product::create([
            'name'  => 'Micrófono Shure SM58',
            'type'  => 'accesorio',
            'stock' => 5,
            'price' => 7500.00,
        ]);

        $customer = Customer::create([
            'first_name'      => 'Pedro',
            'last_name'       => 'QR',
            'document_number' => '33444555',
            'email'           => 'pedro@qr.com',
            'phone'           => '3764123123',
            'address'         => 'Calle 2',
            'city_id'         => 1,
        ]);

        $invoice = Invoice::create([
            'invoice_type'   => 'B',
            'point_of_sale'  => '0001',
            'receipt_number' => '00000099',
            'issue_date'     => now()->subHours(25),
            'due_date'       => now()->addDays(15),
            'total'          => 15000,
            'store_id'       => 1,
            'customer_id'    => $customer->id,
            'employee_id'    => 1,
            'payment_status' => 'pending',
            'mp_order_id'    => 'QR-TEST-999',
        ]);

        // Simula fecha de creación hace más de 24 horas
        $invoice->created_at = now()->subHours(25);
        $invoice->saveQuietly();

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'unit_price' => 7500,
            'subtotal'   => 15000,
        ]);

        // Al crear el detalle, el stock bajó a 3
        $product->refresh();
        $this->assertEquals(3, $product->stock);

        // Ejecutar comando de cancelación de QR expirados
        Artisan::call('invoices:cancel-expired-qr');

        $invoice->refresh();
        $this->assertTrue($invoice->trashed()); // Soft-deleted
        $this->assertEquals('failed', $invoice->payment_status);

        // El stock fue devuelto automáticamente a 5
        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }
}
