<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAddressCatalogStoreRequest extends FormRequest
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
            'customer_id' => ['required', 'integer','exists:customers,id'],
            'address_reference' => ['required', 'string'],
            'is_active' => ['required'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'contact' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'string'],
        ];
    }
}
