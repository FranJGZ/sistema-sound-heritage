<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\InvoiceDetail;
class InvoiceDetailSeeder extends Seeder {
    public function run() { 
        $details = [
            ['quantity' => 1, 'unit_price' => 1200000, 'subtotal' => 1200000, 'invoice_id' => 1, 'product_id' => 1], // 1 Guitarra Electrica
            ['quantity' => 2, 'unit_price' => 15000, 'subtotal' => 30000, 'invoice_id' => 2, 'product_id' => 3], // 2 Paquetes de Cuerdas
            ['quantity' => 1, 'unit_price' => 150000, 'subtotal' => 150000, 'invoice_id' => 3, 'product_id' => 2], // 1 Microfono Shure
            ['quantity' => 2, 'unit_price' => 45000, 'subtotal' => 90000, 'invoice_id' => 4, 'product_id' => 4], // 2 Vinilos de Pink Floyd
            ['quantity' => 1, 'unit_price' => 250000, 'subtotal' => 250000, 'invoice_id' => 5, 'product_id' => 5] // 1 Amplificador Marshall
        ];
        foreach ($details as $det) { InvoiceDetail::create($det); }
    }
}