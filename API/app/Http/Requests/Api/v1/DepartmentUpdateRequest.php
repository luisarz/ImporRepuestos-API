<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentUpdateRequest extends FormRequest
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
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'code' => ['required', 'string', 'max:50'], // Ejemplo de límite de caracteres
            'description' => ['required', 'string', 'max:255'], // Evitar descripciones demasiado largas
            'is_active' => ['required']
        ];

    }
}
