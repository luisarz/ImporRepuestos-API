<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class PurchasesHeaderStoreRequest extends FormRequest
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
            'warehouse' => ['required', 'integer'],
            'quote_purchase_id' => ['nullable', 'integer'],
            'provider_id' => ['required', 'integer'],
            'purchcase_date' => ['required', 'date'],
            'serie' => ['required', 'string'],
            'purchase_number' => ['required', 'string'],
            'resolution' => ['required', 'string'],
            'purchase_type' => ['required', 'integer'],
            'paymen_method' => ['required', 'in:1,2'],
            'payment_status' => ['required', 'in:1,2,3'],
            'net_amount' => ['required', 'numeric'],
            'tax_amount' => ['required', 'numeric'],
            'retention_amount' => ['required', 'numeric'],
            'total_purchase' => ['required', 'numeric'],
            'employee_id' => ['required', 'integer'],
            'status_purchase' => ['required', 'in:1,2,3'],
        ];
    }
}
