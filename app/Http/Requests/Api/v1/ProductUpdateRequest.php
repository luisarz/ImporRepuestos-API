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
                'code' => 'required|string',
                'original_code' => 'nullable|string',
                'barcode' => 'nullable|string',
                'description' => 'required|string',
                'brand_id' => 'required|integer',
                'category_id' => 'required|integer',
                'unit_measurement_id' => 'required|integer',
                'description_measurement_id' => 'required|string',
                'is_active' => 'required|in:0,1',
                'is_taxed' => 'required|in:0,1',
                'is_service' => 'required|in:0,1',
                'is_discontinued' => 'required|in:0,1',
                'is_not_purchasable' => 'required|in:0,1',
        ];
    }
}
