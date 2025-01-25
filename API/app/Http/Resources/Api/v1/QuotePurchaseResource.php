<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotePurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method' => $this->payment_method,
            'provider' => $this->provider,
            'date' => $this->date,
            'amount_purchase' => $this->amount_purchase,
            'is_active' => $this->is_active,
            'is_purchased' => $this->is_purchased,
            'is_compared' => $this->is_compared,
            'buyer_id' => $this->buyer_id,
        ];
    }
}
