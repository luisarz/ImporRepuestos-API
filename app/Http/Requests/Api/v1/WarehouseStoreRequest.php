<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseStoreRequest extends FormRequest
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
            'company_id' => ['required', 'integer'],
            'stablishment_type_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'nrc' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'district_id' => ['required', 'integer'],
            'economic_activity_id' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'product_prices' => ['required', 'integer'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // max 5MB
            'establishment_type_code' => ['required', 'string', 'size:4'],
            'pos_terminal_code' => ['required', 'string', 'size:4'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
