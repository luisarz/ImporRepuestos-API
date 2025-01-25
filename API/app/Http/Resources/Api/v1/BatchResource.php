<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'origen_code' => $this->origen_code,
            'inventory_id' => $this->inventory_id,
            'incoming_date' => $this->incoming_date,
            'expiration_date' => $this->expiration_date,
            'initial_quantity' => $this->initial_quantity,
            'available_quantity' => $this->available_quantity,
            'observations' => $this->observations,
            'is_active' => $this->is_active,
        ];
    }
}
