<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class QuotePurchaseStoreRequest extends FormRequest
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
            'payment_method' => ['required', 'integer'],
            'provider' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'amount_purchase' => ['required', 'numeric'],
            'is_active' => ['required'],
            'is_purchased' => ['required'],
            'is_compared' => ['required'],
            'buyer_id' => ['required', 'integer'],
        ];
    }
}
