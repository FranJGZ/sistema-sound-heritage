<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Product;
class ProductSeeder extends Seeder {
    public function run() { 
        $products = [
            ['type' => 'Instrumento', 'name' => 'Guitarra Eléctrica Fender Stratocaster', 'price' => 1200000, 'stock' => 5],
            ['type' => 'Audio Pro', 'name' => 'Micrófono Shure SM58', 'price' => 150000, 'stock' => 15],
            ['type' => 'Accesorio', 'name' => 'Encordado Ernie Ball Regular Slinky', 'price' => 15000, 'stock' => 50],
            ['type' => 'Disco', 'name' => 'Vinilo Pink Floyd - Dark Side of the Moon', 'price' => 45000, 'stock' => 10],
            ['type' => 'Amplificación', 'name' => 'Amplificador Marshall MG15', 'price' => 250000, 'stock' => 8]
        ];
        foreach ($products as $prod) { Product::create($prod); }
    }
}