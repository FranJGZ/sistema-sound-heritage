<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
class InvoiceSeeder extends Seeder {
    public function run() { 
        $invoices = [
            ['invoice_type' => 'A', 'point_of_sale' => '0001', 'receipt_number' => '00000001', 'issue_date' => '2026-03-01', 'due_date' => '2026-03-10', 'total' => 1200000, 'store_id' => 1, 'customer_id' => 2, 'employee_id' => 2], // Banda compra Guitarra
            ['invoice_type' => 'B', 'point_of_sale' => '0001', 'receipt_number' => '00000002', 'issue_date' => '2026-03-02', 'due_date' => '2026-03-12', 'total' => 30000, 'store_id' => 1, 'customer_id' => 1, 'employee_id' => 2], // Musico compra cuerdas
            ['invoice_type' => 'B', 'point_of_sale' => '0001', 'receipt_number' => '00000003', 'issue_date' => '2026-03-03', 'due_date' => '2026-03-13', 'total' => 150000, 'store_id' => 1, 'customer_id' => 3, 'employee_id' => 2], // Productor compra mic
            ['invoice_type' => 'B', 'point_of_sale' => '0001', 'receipt_number' => '00000004', 'issue_date' => '2026-03-04', 'due_date' => '2026-03-14', 'total' => 90000, 'store_id' => 1, 'customer_id' => 4, 'employee_id' => 2], // Melomana compra vinilos
            ['invoice_type' => 'A', 'point_of_sale' => '0001', 'receipt_number' => '00000005', 'issue_date' => '2026-03-05', 'due_date' => '2026-03-15', 'total' => 250000, 'store_id' => 1, 'customer_id' => 5, 'employee_id' => 2] // Bajista compra ampli
        ];
        foreach ($invoices as $inv) { Invoice::create($inv); }
    }
}