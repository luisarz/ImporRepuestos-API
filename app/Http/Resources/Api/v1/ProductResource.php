<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'original_code' => $this->original_code,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'provider_id' => $this->provider_id,
            'unit_measurement_id' => $this->unit_measurement_id,
            'description_measurement_id' => $this->description_measurement_id,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_taxed' => $this->is_taxed,
            'is_service' => $this->is_service,
        ];
    }
}
