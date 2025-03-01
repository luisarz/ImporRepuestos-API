<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ProviderUpdateRequest extends FormRequest
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
            'legal_name' => ['required', 'string'],
            'comercial_name' => ['required', 'string'],
            'document_type_id' => ['required', 'integer'],
            'document_number' => ['required', 'string'],
            'economic_activity_id' => ['required', 'integer'],
            'provider_type_id' => ['required', 'integer'],
            'payment_type_id' => ['required', 'integer'],
            'credit_days' => ['required', 'integer'],
            'credit_limit' => ['required', 'numeric'],
            'debit_balance' => ['required', 'numeric'],
            'last_purchase' => ['required', 'date'],
            'decimal_purchase' => ['required', 'integer'],
            'is_active' => ['required'],
        ];
    }
}
