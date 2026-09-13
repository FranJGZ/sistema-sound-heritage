<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PurchaseDetail;
class PurchaseDetailSeeder extends Seeder {
    public function run() { 
        $details = [
            ['quantity' => 5, 'price' => 1200000, 'purchase_id' => 1, 'product_id' => 1],
            ['quantity' => 15, 'price' => 150000, 'purchase_id' => 2, 'product_id' => 2],
            ['quantity' => 50, 'price' => 15000, 'purchase_id' => 3, 'product_id' => 3],
            ['quantity' => 10, 'price' => 45000, 'purchase_id' => 4, 'product_id' => 4],
            ['quantity' => 8, 'price' => 250000, 'purchase_id' => 5, 'product_id' => 5]
        ];
        foreach ($details as $det) { PurchaseDetail::create($det); }
    }
}