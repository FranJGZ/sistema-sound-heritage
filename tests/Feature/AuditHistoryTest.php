<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\AuditResource;
use App\Filament\Admin\Resources\AuditResource\Pages\ListAudits;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class AuditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seven_main_models_implement_auditable(): void
    {
        $this->assertInstanceOf(Auditable::class, new Invoice());
        $this->assertInstanceOf(Auditable::class, new \App\Models\Customer());
        $this->assertInstanceOf(Auditable::class, new Purchase());
        $this->assertInstanceOf(Auditable::class, new Product());
        $this->assertInstanceOf(Auditable::class, new Supplier());
        $this->assertInstanceOf(Auditable::class, new \App\Models\Employee());
        $this->assertInstanceOf(Auditable::class, new User());
    }

    public function test_audit_resource_list_page_and_tabs_render_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Admin Test']);

        Audit::create([
            'user_type'      => User::class,
            'user_id'        => $user->id,
            'event'          => 'created',
            'auditable_type' => Invoice::class,
            'auditable_id'   => 15,
            'old_values'     => [],
            'new_values'     => [
                'invoice_type'   => 'B',
                'point_of_sale'  => '0001',
                'receipt_number' => '00000015',
                'total'          => 6210000.00,
            ],
            'url'            => 'https://example.com/livewire/update',
            'ip_address'     => '190.221.255.202',
        ]);

        Livewire::test(ListAudits::class)
            ->assertSuccessful()
            ->assertSee('Admin Test')
            ->assertSee('Creado')
            ->assertSee('Ventas')
            ->set('activeTab', 'ventas')
            ->assertSuccessful()
            ->assertSee('Admin Test');
    }

    public function test_attribute_translation_and_formatting(): void
    {
        $formatted = AuditResource::formatValuesForDisplay([
            'total'          => 150000.50,
            'invoice_type'   => 'B',
            'receipt_number' => '00000015',
            'issue_date'     => '2026-10-14',
            'stock'          => 25,
        ]);

        $this->assertArrayHasKey('Total Facturado', $formatted);
        $this->assertSame('$ 150.000,50', $formatted['Total Facturado']);
        $this->assertSame('14/10/2026', $formatted['Fecha de Emisión']);
        $this->assertSame('25', $formatted['Stock Actual']);
    }

    public function test_reservation_does_not_implement_auditable(): void
    {
        $this->assertNotInstanceOf(Auditable::class, new \App\Models\Reservation());
    }

    public function test_audit_diff_display_for_updated_event(): void
    {
        $audit = new Audit([
            'event'          => 'updated',
            'auditable_type' => Product::class,
            'auditable_id'   => 1,
            'old_values'     => [
                'price' => 100000.0,
                'stock' => 10,
            ],
            'new_values'     => [
                'price' => 125000.0,
                'stock' => 8,
            ],
        ]);

        $diff = AuditResource::getDiffDisplay($audit);

        $this->assertArrayHasKey('Precio de Venta', $diff);
        $this->assertArrayHasKey('Stock Actual', $diff);
        $this->assertEquals('$ 100.000,00  ➜  $ 125.000,00', $diff['Precio de Venta']);
        $this->assertEquals('10  ➜  8', $diff['Stock Actual']);
    }
}
