<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class MunicipalityUpdateRequest extends FormRequest
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
            'department_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
            'description' => ['required', 'string'],
            'is_active' => ['required'],
        ];
    }
}
