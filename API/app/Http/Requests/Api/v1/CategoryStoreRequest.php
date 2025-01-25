<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'commission_percentage' => ['nullable', 'numeric'],
            'category_parent_id' => ['required', 'integer'],
            'is_active' => ['required'],
        ];
    }
}
