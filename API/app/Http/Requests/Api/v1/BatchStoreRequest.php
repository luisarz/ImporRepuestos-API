<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class BatchStoreRequest extends FormRequest
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
            'code' => ['required', 'string'],
            'origen_code' => ['required', 'numeric','exists:batch_code_origens,id'],
            'inventory_id' => ['required', 'integer','exists:inventories,id'],
            'incoming_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date'],
            'initial_quantity' => ['required', 'numeric'],
            'available_quantity' => ['required', 'numeric'],
            'observations' => ['required', 'string'],
            'is_active' => ['required'],
        ];
    }
}
