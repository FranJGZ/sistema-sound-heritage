<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
class PaymentMethodSeeder extends Seeder {
    public function run() { 
        $methods = [['name' => 'Efectivo'], 
        ['name' => 'Tarjeta de Débito'], 
        ['name' => 'Tarjeta de Crédito'], 
        ['name' => 'Transferencia Bancaria'], 
        ['name' => 'Mercado Pago']];
        foreach ($methods as $met) { PaymentMethod::create($met); }
    }
}