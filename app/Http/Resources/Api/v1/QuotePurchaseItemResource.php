<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotePurchaseItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_purchase_id' => $this->quote_purchase_id,
            'inventory_id' => $this->inventory_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'total' => $this->total,
            'is_compared' => $this->is_compared,
            'is_purchased' => $this->is_purchased,
            'description' => $this->description,
        ];
    }
}
