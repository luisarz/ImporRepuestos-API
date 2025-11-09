<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'email' => 'required|email|unique:users,email,' . $this->route('user'),
            'employee_id' => ['required', 'exists:employees,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'rememeber_token' => ['nullable', 'string'],
            'roles' => ['required', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
