<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'code' => ['required', 'string'],
            'original_code' => ['required', 'string'],
            'barcode' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'unit_measurement_id' => ['nullable', 'integer', 'exists:unit_measurements,id'],
            'description_measurement_id' => ['required', 'string'],
            'image' => ['nullable', 'json'],
            'is_active' => ['required'],
            'is_taxed' => ['required'],
            'is_service' => ['required'],
        ];
    }
}
