<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ModuloStoreRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'icono' => ['required'],
            'ruta' => ['required','string'],
            'id_padre' => ['nullable', 'integer'],
            'is_padre' => ['required', 'boolean'],
            'orden' => ['required', 'integer'],
            'is_minimazed' => ['required', 'boolean'],
            'target' => ['required', 'integer'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
