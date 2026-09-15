<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <- VITAL PARA LAS TRANSACCIONES

class PurchaseController extends Controller
{
    public function index()
    {
        // withTrashed() trae también las compras anuladas.
        // with() hace "Eager Loading" para traer los datos del proveedor y los detalles de un solo golpe.
        $purchases = Purchase::withTrashed()
                        ->with(['supplier', 'purchaseDetails'])
                        ->get();

        return response()->json($purchases, 200);
    }

    // Mostrar una sola compra en detalle
    public function show($id)
    {
        // Buscamos la compra por ID (incluso si está anulada)
        $purchase = Purchase::withTrashed()
                        ->with(['supplier', 'purchaseDetails'])
                        ->findOrFail($id);

        return response()->json($purchase, 200);
    }
    public function store(Request $request)
    {
        // 1. Validación de los datos que envía el frontend
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_date'        => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        try {
            // 2. INICIAMOS LA TRANSACCIÓN (ACID Compliance)
            DB::transaction(function () use ($request) {
                
                // A. Creamos la factura (cabecera)
                $purchase = Purchase::create([
                    'supplier_id'   => $request->supplier_id,
                    'purchase_date' => $request->purchase_date,
                    'total'         => 0 // Lo calculamos ahora
                ]);

                $totalCalculado = 0;

                // B. Recorremos los ítems comprados
                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $totalCalculado += $subtotal;

                    // Usamos la relación para crear el detalle
                    $purchase->purchaseDetails()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);

                    // C. LA MAGIA DEL STOCK: Sumamos físicamente al producto
                    Product::where('id', $item['product_id'])
                           ->increment('stock', $item['quantity']);
                }

                // D. Actualizamos la factura con el total real
                $purchase->update(['total' => $totalCalculado]);
            });

            return response()->json([
                'message' => 'Compra registrada y stock actualizado con éxito.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar la compra. Se revirtieron los cambios.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ANULAR COMPRA (SOFT DELETE) Y RESTAR STOCK (ROLLBACK)
     */
   public function destroy($id) // 1. Cambiamos lo que recibe entre paréntesis
    {
        try {
            // 2. Buscamos la factura real en la base de datos a mano
            $purchase = Purchase::findOrFail($id);

            // TRANSACCIÓN para asegurar que ambas cosas pasen juntas
            DB::transaction(function () use ($purchase) {
                
                // 1. ROLLBACK DEL STOCK: Recorremos los detalles
                foreach ($purchase->purchaseDetails as $detail) {
                    Product::where('id', $detail->product_id)
                           ->decrement('stock', $detail->quantity);
                }

                // 2. ANULAR FACTURA (Soft Delete)
                $purchase->delete();
            });

            return response()->json([
                'message' => 'Compra anulada. El stock ha vuelto a la normalidad.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al anular la compra.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}