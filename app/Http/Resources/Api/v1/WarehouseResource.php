<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'stablishment_type' => $this->stablishment_type,
            'name' => $this->name,
            'nrc' => $this->nrc,
            'nit' => $this->nit,
            'district_id' => $this->district_id,
            'economic_activity_id' => $this->economic_activity_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'product_prices' => $this->product_prices,
            'logo' => $this->logo,
        ];
    }
}
