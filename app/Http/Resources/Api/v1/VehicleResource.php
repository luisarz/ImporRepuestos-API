<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'model_id' => $this->model_id,
            'model_two' => $this->model_two,
            'year' => $this->year,
            'chassis' => $this->chassis,
            'vin' => $this->vin,
            'motor' => $this->motor,
            'displacement' => $this->displacement,
            'motor_type' => $this->motor_type,
            'fuel_type' => $this->fuel_type,
            'vehicle_class' => $this->vehicle_class,
            'income_date' => $this->income_date,
            'municipality_id' => $this->municipality_id,
            'antique' => $this->antique,
            'plate_type' => $this->plate_type,
            'capacity' => $this->capacity,
            'tonnage' => $this->tonnage,
            'is_active' => $this->is_active,
        ];
    }
}
