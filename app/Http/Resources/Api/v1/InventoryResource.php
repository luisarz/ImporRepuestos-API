<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'last_cost_without_tax' => $this->last_cost_without_tax,
            'last_cost_with_tax' => $this->last_cost_with_tax,
            'stock_actual_quantity' => $this->stock_actual_quantity,
            'stock_min' => $this->stock_min,
            'alert_stock_min' => $this->alert_stock_min,
            'stock_max' => $this->stock_max,
            'alert_stock_max' => $this->alert_stock_max,
            'last_purchase' => $this->last_purchase,
            'is_service' => $this->is_service,
        ];
    }
}
