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
            'customer_type_id' => ['required', 'integer'],
            'document_type_id' => ['required', 'integer'],
            'document_number' => ['required', 'string'],
            'economic_activity_id'=>['required','integer'],
            'country_id'=>['required','integer'],
            'departament_id'=>['required','integer'],
            'municipality_id'=>['required','integer'],
            'phone'=>['required','integer'],
            'email'=>['required','integer'],
            'name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'warehouse_id' => ['required', 'integer','exists:warehouses,id'],
            'nrc' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'is_exempt' => ['required'],
            'sales_type' => ['required', 'in:1,2,3,4'],
//            'is_creditable' => ['required'],
            'address' => ['required', 'string'],
            'credit_limit' => [ 'numeric'],
            'credit_amount' => [ 'numeric'],
//            'is_delivery' => ['required'],
            'is_active' => ['required'],
        ];
    }
}
