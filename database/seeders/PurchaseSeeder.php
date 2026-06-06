<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Purchase;
class PurchaseSeeder extends Seeder {
    public function run() { 
        $purchases = [
            ['purchase_date' => '2026-01-10', 'total' => 6000000, 'supplier_id' => 1],
            ['purchase_date' => '2026-01-15', 'total' => 2250000, 'supplier_id' => 2],
            ['purchase_date' => '2026-02-05', 'total' => 750000, 'supplier_id' => 3],
            ['purchase_date' => '2026-02-20', 'total' => 450000, 'supplier_id' => 4],
            ['purchase_date' => '2026-03-01', 'total' => 2000000, 'supplier_id' => 5]
        ];
        foreach ($purchases as $purch) { Purchase::create($purch); }
    }
}