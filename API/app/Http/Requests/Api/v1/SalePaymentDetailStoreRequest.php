<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class SalePaymentDetailStoreRequest extends FormRequest
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
            'payment_method_id' => ['required', 'integer'],
            'casher_id' => ['required', 'integer'],
            'payment_amount' => ['required', 'numeric'],
            'actual_balance' => ['required', 'numeric'],
            'bank_account_id' => ['required', 'integer'],
            'reference' => ['required', 'string'],
            'is_active' => ['required'],
        ];
    }
}
