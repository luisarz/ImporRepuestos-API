<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class InventoryUpdateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'last_cost_without_tax' => ['required', 'numeric'],
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'last_cost_with_tax' => ['required', 'numeric'],
            'stock_actual_quantity' => ['required', 'numeric'],
            'stock_min' => ['required', 'numeric'],
            'is_temp' => ['sometimes', 'boolean'], // ← Permitir actualización de is_temp
            'alert_stock_min' => ['sometimes', 'boolean'],
            'stock_max' => ['sometimes', 'numeric'],
            'alert_stock_max' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
    public function attributes(): array
    {
        return [
            'last_cost_without_tax' => 'costo sin IVA',
            'last_cost_with_tax' => 'costo con IVA',
            'warehouse_id' => 'sucursal',
            'product_id' => 'producto',
            'provider_id' => 'proveedor',
            'stock_actual_quantity' => 'inventario actual',
            'stock_min' => 'stock mínimo',
            'alert_stock_min' => 'alerta stock mínimo',
            'stock_max' => 'stock máximo',
            'alert_stock_max' => 'alerta stock máximo',
            'last_purchase' => 'última compra',
        ];
    }
    public function messages(): array
    {
        return [
            'last_cost_without_tax.numeric' => 'El campo :attribute debe ser numérico.',
            'last_cost_with_tax.numeric' => 'El campo :attribute debe ser numérico.',
            'warehouse_id.required' => 'La :attribute es obligatoria.',
            'product_id.required' => 'El :attribute es obligatorio.',
            'provider_id.required' => 'El :attribute es obligatorio.',
            // puedes agregar más mensajes personalizados si lo deseas
        ];
    }
}
