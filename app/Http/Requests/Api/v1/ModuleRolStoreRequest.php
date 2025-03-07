<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRolStoreRequest extends FormRequest
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
            'id_module' => ['required','integer','exists:modulo,id'],
            'id_rol' => ['required','integer','exists:roles,id'],
            'is_active' => ['boolean', 'required']
        ];
    }
}
