<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoriesBatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_inventory' => $this->id_inventory,
            'id_batch' => $this->id_batch,
            'quantity' => $this->quantity,
            'operation_date' => $this->operation_date,
        ];
    }
}
