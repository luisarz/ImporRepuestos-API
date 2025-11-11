<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateRequest extends FormRequest
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
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'job_title_id' => ['required', 'integer', 'exists:jobs_titles,id'],
            'name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'in:M,F'],
            'dui' => ['required', 'string', 'max:10'],
            'nit' => ['nullable', 'string', 'max:17'],
            'phone' => ['required', 'string', 'max:9'],
            'email' => ['required', 'email'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // max 5MB
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'address' => ['required', 'string'],
            'comision_porcentage' => ['required', 'numeric'],
            'is_active' => ['required'],
            'marital_status' => ['required', 'in:Soltero/a,Casado/a,Divorciado/a,Viudo'],
            'marital_name' => ['required', 'string'],
            'marital_phone' => ['required', 'string'],
        ];
    }
}
