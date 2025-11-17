<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class TransferStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transfer_date' => 'required|date',
            'warehouse_origin_id' => 'required|exists:warehouses,id',
            'warehouse_destination_id' => 'required|exists:warehouses,id|different:warehouse_origin_id',
            'observations' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.inventory_origin_id' => 'required|exists:inventories,id',
            'items.*.inventory_destination_id' => 'required|exists:inventories,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'warehouse_destination_id.different' => 'El almacén destino debe ser diferente al almacén origen',
            'items.required' => 'Debe agregar al menos un producto al traslado',
            'items.min' => 'Debe agregar al menos un producto al traslado',
        ];
    }
}
