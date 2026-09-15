<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // DICCIONARIO DE MOLDES (TEMPLATES)
    private $templates = [
        'Guitarra' => ['madera_cuerpo', 'cantidad_trastes', 'tipo_microfonos'],
        'Teclado'  => ['cantidad_teclas', 'tiene_midi', 'polifonia'],
        'Bateria'  => ['cantidad_cuerpos', 'material_cascos']
    ];

    public function index()
    {
        return response()->json(Product::all(), 200);
    }

    public function show(Product $product)
    {
        return response()->json($product, 200);
    }

    // CREACIÓN RÁPIDA (DESDE EL MODAL)
    public function storeQuick(Request $request)
    {
        $request->validate([
            'type'          => 'required|string', 
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'template_data' => 'nullable|array'
        ]);

        $tipo = $request->type;
        $datosUsuario = $request->template_data ?? [];
        $specsFinales = [];

        // Rellenamos el molde de forma segura
        if (array_key_exists($tipo, $this->templates)) {
            $molde = $this->templates[$tipo];
            foreach ($molde as $atributo) {
                $specsFinales[$atributo] = $datosUsuario[$atributo] ?? 'No especificado';
            }
        }

        // Creamos el producto (El stock nace en 0 por tu BD)
        $product = Product::create([
            'type'  => $tipo,
            'name'  => $request->name,
            'price' => $request->price,
            'specs' => empty($specsFinales) ? null : $specsFinales 
        ]);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'product' => $product
        ], 201);
    }

    // ELIMINAR UN PRODUCTO CON SEGURIDAD
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->stock > 0) {
            return response()->json(['error' => 'No puedes eliminar un instrumento que tiene stock disponible.'], 403);
        }

        if (PurchaseDetail::where('product_id', $id)->exists()) {
            return response()->json(['error' => 'Este producto está en facturas pasadas. No se puede borrar.'], 403);
        }

        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente.'], 200);
    }
}