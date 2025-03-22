<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
            'economic_activity_id' => ['required', 'integer'],
            'company_name' => ['required', 'string'],
            'nrc' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'whatsapp' => ['required', 'string'],
            'email' => ['required', 'email'],
            'address' => ['required', 'string'],
            'web' => ['required', 'string'],
            'api_key_mh' => ['required', 'string'],
            'logo' => ['nullable'],
            'is_active' => ['required'],
        ];
    }
}
