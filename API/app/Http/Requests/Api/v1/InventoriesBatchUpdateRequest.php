<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class InventoriesBatchUpdateRequest extends FormRequest
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
            'id_inventory' => ['required', 'integer'],
            'id_batch' => ['required', 'integer'],
            'quantity' => ['required', 'numeric'],
            'operation_date' => ['required'],
        ];
    }
}
