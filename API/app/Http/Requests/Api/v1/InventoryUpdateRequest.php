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
            'warehouse_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'last_cost_without_tax' => ['required', 'numeric'],
            'last_cost_with_tax' => ['required', 'numeric'],
            'stock_actual_quantity' => ['required', 'numeric'],
            'stock_min' => ['required', 'numeric'],
            'alert_stock_min' => ['required'],
            'stock_max' => ['required', 'numeric'],
            'alert_stock_max' => ['required'],
            'last_purchase' => ['required'],
            'is_service' => ['required'],
        ];
    }
}
