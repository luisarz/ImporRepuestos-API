<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class HistoryDteUpdateRequest extends FormRequest
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
            'sale_dte_id' => ['required', 'integer'],
            'version' => ['nullable', 'string'],
            'ambiente' => ['nullable', 'string'],
            'status' => ['required', 'in:1,2'],
            'code_generation' => ['nullable', 'string'],
            'receipt_stamp' => ['nullable', 'string'],
            'fhProcesamiento' => ['nullable'],
            'clasifica_msg' => ['nullable', 'string'],
            'code_mgs' => ['nullable', 'string'],
            'description_msg' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'dte' => ['nullable', 'string'],
        ];
    }
}
