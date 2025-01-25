<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class VehicleUpdateRequest extends FormRequest
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
            'brand_id' => ['required', 'integer'],
            'model_id' => ['required', 'integer'],
            'model_two' => ['required', 'string'],
            'year' => ['required', 'string'],
            'chassis' => ['required', 'string'],
            'vin' => ['required', 'string'],
            'motor' => ['required', 'string'],
            'displacement' => ['required', 'string'],
            'motor_type' => ['required', 'string'],
            'fuel_type' => ['required', 'integer'],
            'vehicle_class' => ['required', 'string'],
            'income_date' => ['required', 'date'],
            'municipality_id' => ['required', 'integer'],
            'antique' => ['required', 'string'],
            'plate_type' => ['required', 'integer'],
            'capacity' => ['required', 'numeric'],
            'tonnage' => ['required', 'numeric'],
            'is_active' => ['required'],
        ];
    }
}
