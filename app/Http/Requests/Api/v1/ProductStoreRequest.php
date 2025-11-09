<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255'],
            'original_code' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'unit_measurement_id' => ['required', 'integer', 'exists:unit_measurements,id'],
            'description_measurement_id' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
            'is_active' => ['required', 'boolean'],
            'is_taxed' => ['required', 'boolean'],
            'is_service' => ['required', 'boolean'],
            'is_discontinued' => ['required', 'boolean'],
            'is_not_purchasable' => ['required', 'boolean'],
            // is_temp no debe venir del frontend - lo establecemos en el controlador
        ];
    }
}
