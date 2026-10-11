<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    return [
        // El proveedor debe ser obligatorio y existir en la tabla suppliers
        'supplier_id' => 'required|integer|exists:suppliers,id',
        
        // La fecha es obligatoria
        'purchase_date' => 'required|date',
        
        // Tiene que venir un arreglo de 'items' con al menos 1 producto
        'items' => 'required|array|min:1',
        
        // Validaciones para CADA ítem dentro del arreglo
        'items.*.product_id' => 'required|integer|exists:products,id', // El producto debe existir
        'items.*.quantity' => 'required|integer|min:1',                // Mínimo 1 unidad
        'items.*.unit_price' => 'required|numeric|min:0',              // Precio no puede ser negativo
    ];
    }
}
