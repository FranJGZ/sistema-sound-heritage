<?php

namespace Tests\Feature;

use App\Livewire\MiCuenta;
use App\Models\Attendance;
use App\Models\City;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);

        $province = Province::create(['name' => 'Misiones']);
        $this->city = City::create(['name' => 'Posadas', 'province_id' => $province->id]);

        $this->user = User::factory()->create([
            'name'     => 'Esteban Quito',
            'email'    => 'esteban@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->customer = Customer::create([
            'user_id'         => $this->user->id,
            'first_name'      => 'Esteban',
            'last_name'       => 'Quito',
            'document_number' => '33444555',
            'tax_id'          => '20334445558',
            'tax_condition'   => 'Consumidor Final',
            'address'         => 'Av. San Martín 1500',
            'phone'           => '3764000111',
            'email'           => 'esteban@example.com',
            'city_id'         => $this->city->id,
        ]);
    }

    public function test_mi_cuenta_dashboard_renders_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('mi-cuenta'));
        $response->assertOk();
        $response->assertSee('Mi cuenta');
        $response->assertSee('Mis reservas');
        $response->assertSee('Mis Facturas');
        $response->assertSee('Mis domicilios');
        $response->assertSee('Mis cuentas / Datos personales');
        $response->assertSee('Mis Entradas');
        $response->assertSee('Seguridad de la cuenta');
    }

    public function test_can_update_personal_data_without_altering_sensitive_fiscal_data(): void
    {
        $this->actingAs($this->user);

        Livewire::test(MiCuenta::class)
            ->set('first_name', 'Esteban Daniel')
            ->set('last_name', 'Quito Pérez')
            ->set('phone', '3764999888')
            ->call('actualizarDatos')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Tus datos personales fueron actualizados correctamente.');

        $this->customer->refresh();
        $this->assertEquals('Esteban Daniel', $this->customer->first_name);
        $this->assertEquals('Quito Pérez', $this->customer->last_name);
        $this->assertEquals('3764999888', $this->customer->phone);

        // Sensitive fiscal data remains strictly untouched
        $this->assertEquals('33444555', $this->customer->document_number);
        $this->assertEquals('Consumidor Final', $this->customer->tax_condition);
    }

    public function test_can_update_shipping_address(): void
    {
        $this->actingAs($this->user);

        Livewire::test(MiCuenta::class)
            ->set('address', 'Calle Bolívar 742, Piso 4')
            ->set('city_id', $this->city->id)
            ->call('actualizarDomicilio')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Tu domicilio de entrega fue guardado con éxito.');

        $this->customer->refresh();
        $this->assertEquals('Calle Bolívar 742, Piso 4', $this->customer->address);
    }

    public function test_can_update_password(): void
    {
        $this->actingAs($this->user);

        Livewire::test(MiCuenta::class)
            ->set('current_password', 'password123')
            ->set('new_password', 'NewSecurePass2026')
            ->set('new_password_confirmation', 'NewSecurePass2026')
            ->call('actualizarContrasena')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Tu contraseña fue modificada exitosamente.');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass2026', $this->user->password));
    }

    public function test_can_download_invoice_ticket_pdf(): void
    {
        $this->actingAs($this->user);

        $store = Store::create([
            'name'          => 'Sound Heritage Central',
            'company_name'  => 'Sound Heritage S.R.L.',
            'address'       => 'Av. Corrientes 1234',
            'city_id'       => $this->city->id,
            'tax_condition' => 'Responsable Inscripto',
            'phone'         => '1145678901',
            'tax_id'        => '30712345678',
            'start_date'    => '2020-01-01',
        ]);

        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('Vendedor');
        $category = EmployeeCategory::firstOrCreate(['name' => 'Vendedor']);

        $employee = Employee::create([
            'user_id'              => $employeeUser->id,
            'store_id'             => $store->id,
            'document_number'      => '20300000',
            'first_name'           => 'Vendedor',
            'last_name'            => 'Test',
            'phone'                => '1145678902',
            'address'              => 'Av. Corrientes 1235',
            'city_id'              => $this->city->id,
            'employee_category_id' => $category->id,
        ]);

        $invoice = Invoice::create([
            'store_id'      => $store->id,
            'employee_id'   => $employee->id,
            'customer_id'   => $this->customer->id,
            'invoice_type'  => 'B',
            'point_of_sale' => 1,
            'receipt_number'=> 105,
            'issue_date'    => now()->format('Y-m-d'),
            'due_date'      => now()->addDays(15)->format('Y-m-d'),
            'total'         => 850000,
            'tax_condition' => 'Consumidor Final',
        ]);

        $product = Product::create([
            'type'                => 'cuerda',
            'name'                => 'Guitarra Yamaha Pacifica',
            'brand'               => 'Yamaha',
            'model'               => 'Pacifica 112V',
            'price'               => 850000,
            'discount_percentage' => 0,
            'stock'               => 10,
        ]);

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'unit_price' => 850000,
            'subtotal'   => 850000,
        ]);

        $component = Livewire::test(MiCuenta::class);
        $response = $component->call('descargarFacturaPdf', $invoice->id);

        $this->assertNotNull($response);
    }
}
