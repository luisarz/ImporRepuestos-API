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
            'stablishment_type' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'nrc' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'district_id' => ['required', 'integer'],
            'economic_activity_id' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'product_prices' => ['required', 'integer'],
            'logo' => ['nullable', 'json'],
        ];
    }
}
