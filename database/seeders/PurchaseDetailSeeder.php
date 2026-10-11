<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Database\Seeder;

class PurchaseDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::withTrashed()->get()->keyBy('id');

        PurchaseDetail::withoutEvents(function () use ($products) {
            for ($purchaseId = 1; $purchaseId <= 50; $purchaseId++) {
                $product1 = $products->get($purchaseId);
                $product2 = $products->get(($purchaseId % 50) + 1);

                $qty1 = ($purchaseId % 4) + 1;
                $cost1 = round(((float) $product1->price) * 0.65, -2); // Costo ~65% del precio de venta

                PurchaseDetail::create([
                    'purchase_id' => $purchaseId,
                    'product_id'  => $product1->id,
                    'quantity'    => $qty1,
                    'price'       => $cost1,
                ]);

                $total = $qty1 * $cost1;

                // A las compras pares les agregamos un segundo renglón de producto
                if ($purchaseId % 2 === 0 && $product2->id !== $product1->id) {
                    $qty2 = ($purchaseId % 3) + 2;
                    $cost2 = round(((float) $product2->price) * 0.65, -2);

                    PurchaseDetail::create([
                        'purchase_id' => $purchaseId,
                        'product_id'  => $product2->id,
                        'quantity'    => $qty2,
                        'price'       => $cost2,
                    ]);

                    $total += ($qty2 * $cost2);
                }

                Purchase::withTrashed()->where('id', $purchaseId)->update(['total' => $total]);
            }
        });
    }
}