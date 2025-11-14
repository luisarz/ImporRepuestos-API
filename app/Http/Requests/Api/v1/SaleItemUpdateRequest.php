<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class SaleItemUpdateRequest extends FormRequest
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
            'sale_id' => ['sometimes', 'integer'],
            'inventory_id' => ['sometimes', 'integer'],
            'batch_id' => ['nullable', 'integer'],
            'saled' => ['sometimes'],
            'quantity' => ['sometimes', 'numeric'],
            'price' => ['sometimes', 'numeric'],
            'discount' => ['sometimes', 'integer', 'min:0', 'max:25'], // Porcentaje de descuento (0-25)
            'total' => ['sometimes', 'numeric'],
            'observations' => ['nullable', 'string'],
            'is_saled' => ['sometimes'],
            'is_active' => ['sometimes'],
        ];
    }
}
