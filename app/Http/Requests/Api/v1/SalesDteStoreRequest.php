<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class SalesDteStoreRequest extends FormRequest
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
            'sale_id' => ['required', 'integer'],
            'is_dte' => ['required'],
            'generation_code' => ['required', 'integer'],
            'billing_model' => ['required', 'integer'],
            'transmition_type' => ['required', 'integer'],
            'receipt_stamp' => ['required', 'string'],
            'json_url' => ['nullable', 'string'],
            'pdf_url' => ['nullable', 'string'],
        ];
    }
}
