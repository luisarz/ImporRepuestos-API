<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'price_description' => $this->price_description,
            'price' => $this->price,
            'max_discount' => $this->max_discount,
            'is_active' => $this->is_active,
            'quantity' => $this->quantity,
        ];
    }
}
