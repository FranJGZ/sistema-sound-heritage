<?php

namespace Database\Seeders;

use App\Models\Purchase;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $supplierId = (($i - 1) % 5) + 1; // Reparte entre los 5 proveedores
            $daysAgo = (50 - $i) * 3;
            $isAnnulled = in_array($i, [6, 14, 27, 39, 48]); // 5 compras anuladas para probar filtros

            Purchase::create([
                'purchase_date' => now()->subDays($daysAgo)->format('Y-m-d'),
                'total'         => 0, // Se actualiza al cargar los detalles en PurchaseDetailSeeder
                'supplier_id'   => $supplierId,
                'deleted_at'    => $isAnnulled ? now()->subDays(max(1, $daysAgo - 1)) : null,
                'created_at'    => now()->subDays($daysAgo),
                'updated_at'    => now()->subDays($daysAgo),
            ]);
        }
    }
}