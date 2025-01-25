<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ProviderAddressCatalogStoreRequest extends FormRequest
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
            'district_id' => ['required', 'integer'],
            'address_reference' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'seller' => ['nullable', 'string'],
            'seller_phone' => ['nullable', 'string'],
            'seller_email' => ['nullable', 'string'],
            'is_active' => ['required'],
        ];
    }
}
