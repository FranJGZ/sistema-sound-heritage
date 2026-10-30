<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Efectivo'],
            ['name' => 'Transferencia Bancaria'],
            ['name' => 'Mercado Pago'],
        ];

        foreach ($methods as $met) {
            PaymentMethod::firstOrCreate($met);
        }

        // Normalizar cualquier registro histórico ambiguo
        PaymentMethod::where('name', 'Efectivo / Transferencia')
            ->update(['name' => 'Transferencia Bancaria']);
    }
}
