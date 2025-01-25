<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class QuotePurchaseItemUpdateRequest extends FormRequest
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
            'quote_purchase_id' => ['required', 'integer'],
            'inventory_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'is_compared' => ['required', 'integer'],
            'is_purchased' => ['required'],
            'description' => ['nullable', 'string'],
        ];
    }
}
