<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class PriceStoreRequest extends FormRequest
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
            'price' => ['required', 'numeric'],
            'inventory_id' => ['required', 'integer', 'exists:inventories,id'],
            'price_description' => ['required', 'string'],
            'max_discount' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
            'quantity' => ['required', 'numeric'],
        ];
    }
}
