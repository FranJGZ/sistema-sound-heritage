<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el registro de un detalle de compra incremente el stock del producto,
     * y que la anulación (soft delete) de la orden lo descuente adecuadamente.
     */
    public function test_compra_incrementa_el_stock_y_anularla_lo_descuenta(): void
    {
        // 1. Preparar (Arrange): Producto con stock inicial de 10 unidades
        $supplier = Supplier::create([
            'name'  => 'Distribuidora Musical Fender & Gibson',
            'email' => 'ventas@distribuidoramusical.com',
        ]);

        $product = Product::create([
            'name'  => 'Guitarra Eléctrica Fender Stratocaster',
            'type'  => 'cuerda',
            'price' => 450000.00,
            'stock' => 10,
        ]);

        // 2. Ejecutar (Act): Crear la compra y registrar el detalle con 5 unidades
        $purchase = Purchase::create([
            'supplier_id'   => $supplier->id,
            'purchase_date' => now()->format('Y-m-d'),
            'total'         => 150000.00,
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id'  => $product->id,
            'quantity'    => 5,
            'price'       => 30000.00,
        ]);

        // 3. Comprobar (Assert): El stock subió automáticamente a 15
        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseHas('purchases', [
            'id'    => $purchase->id,
            'total' => 150000.00,
        ]);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_details', 1);

        // --- Simular anulación de la orden de compra ---
        $purchase->delete();

        // El stock debe descontarse y volver a los 10 iniciales
        $this->assertSame(10, $product->fresh()->stock);
        // La compra debe figurar como anulada (SoftDeleted)
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    /**
     * Prueba que al restaurar una orden de compra previamente anulada,
     * el stock vuelva a incrementarse.
     */
    public function test_restaurar_compra_anulada_vuelve_a_incrementar_el_stock(): void
    {
        // 1. Preparar
        $supplier = Supplier::create([
            'name' => 'Distribuidora Yamaha Musical',
        ]);

        $product = Product::create([
            'name'  => 'Teclado Sintetizador 61 Teclas',
            'type'  => 'teclado',
            'price' => 600000.00,
            'stock' => 20,
        ]);

        $purchase = Purchase::create([
            'supplier_id'   => $supplier->id,
            'purchase_date' => now()->format('Y-m-d'),
            'total'         => 80000.00,
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->id,
            'product_id'  => $product->id,
            'quantity'    => 4,
            'price'       => 20000.00,
        ]);

        // Comprobamos stock luego del ingreso: 20 + 4 = 24
        $this->assertSame(24, $product->fresh()->stock);

        // Anulamos la compra: stock baja a 20
        $purchase->delete();
        $this->assertSame(20, $product->fresh()->stock);

        // 2. Ejecutar: Restaurar la compra anulada
        $purchase->restore();

        // 3. Comprobar: El stock debe volver a ser 24
        $this->assertSame(24, $product->fresh()->stock);
        $this->assertDatabaseHas('purchases', [
            'id'         => $purchase->id,
            'deleted_at' => null,
        ]);
    }
}

