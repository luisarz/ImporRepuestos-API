<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class PurchasesHeaderUpdateRequest extends FormRequest
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
        $rules = [
            'warehouse' => ['required', 'integer'],
            'quote_purchase_id' => ['nullable', 'integer'],
            'provider_id' => ['nullable', 'integer'],
            'purchase_date' => ['required', 'date'],
            'serie' => ['nullable', 'string'],
            'purchase_number' => ['nullable', 'string'],
            'resolution' => ['nullable', 'string'],
            'purchase_type' => ['nullable', 'integer'],
            'payment_method' => ['required', 'in:1,2'],
            'payment_status' => ['nullable', 'in:1,2,3'],
            'net_amount' => ['nullable', 'numeric'],
            'tax_amount' => ['nullable', 'numeric'],
            'retention_amount' => ['nullable', 'numeric'],
            'total_purchase' => ['nullable', 'numeric'],
            'employee_id' => ['nullable', 'integer'],
            'status_purchase' => ['nullable', 'in:1,2,3'],
        ];

        // Si el estado es "Finalizada" (2), entonces provider_id es obligatorio
        if ($this->input('status_purchase') === '2' || $this->input('status_purchase') === 2) {
            $rules['provider_id'] = ['required', 'integer', 'exists:providers,id'];
            $rules['serie'] = ['required', 'string'];
            $rules['purchase_number'] = ['required', 'string'];
            $rules['resolution'] = ['required', 'string'];
            $rules['net_amount'] = ['required', 'numeric'];
            $rules['tax_amount'] = ['required', 'numeric'];
            $rules['total_purchase'] = ['required', 'numeric'];
        }

        return $rules;
    }
}
