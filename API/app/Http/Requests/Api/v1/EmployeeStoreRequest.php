<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStoreRequest extends FormRequest
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
            'warehouse_id' => ['required', 'integer'],
            'job_title_id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'in:M,F'],
            'dui' => ['required', 'string'],
            'nit' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'photo' => ['nullable', 'json'],
            'district_id' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'comision_porcentage' => ['required', 'numeric'],
            'is_active' => ['required'],
            'marital_status' => ['required', 'in:Soltero/a,Casado/a,Divorciado/a,Viudo'],
            'marital_name' => ['required', 'string'],
            'marital_phone' => ['required', 'string'],
        ];
    }
}
