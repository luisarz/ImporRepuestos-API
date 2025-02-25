<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
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
            'customer_type' => ['required', 'integer'],
            'document_type_id' => ['required', 'integer'],
            'document_number' => ['required', 'string'],
            'name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'warehouse' => ['required', 'integer','exists:warehouses,id'],
            'nrc' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'is_exempt' => ['required'],
            'sales_type' => ['required', 'in:1,2,3,4'],
            'is_creditable' => ['required'],
            'address' => ['required', 'string'],
            'credit_limit' => ['required', 'numeric'],
            'credit_amount' => ['required', 'numeric'],
            'is_delivery' => ['required'],
        ];
    }
}
