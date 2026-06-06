<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PaymentDetail;
class PaymentDetailSeeder extends Seeder {
    public function run() { 
        $payments = [
            ['invoice_id' => 1, 'payment_method_id' => 4], // Guitarra pagada con Transferencia
            ['invoice_id' => 2, 'payment_method_id' => 1], // Cuerdas pagadas en Efectivo
            ['invoice_id' => 3, 'payment_method_id' => 3], // Microfono pagado con Tarjeta Credito
            ['invoice_id' => 4, 'payment_method_id' => 2], // Vinilos pagados con Tarjeta Debito
            ['invoice_id' => 5, 'payment_method_id' => 5]  // Amplificador pagado con Mercado Pago
        ];
        foreach ($payments as $pay) { PaymentDetail::create($pay); }
    }
}