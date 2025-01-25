<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasesHeaderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse' => $this->warehouse,
            'quote_purchase_id' => $this->quote_purchase_id,
            'provider_id' => $this->provider_id,
            'purchcase_date' => $this->purchcase_date,
            'serie' => $this->serie,
            'purchase_number' => $this->purchase_number,
            'resolution' => $this->resolution,
            'purchase_type' => $this->purchase_type,
            'paymen_method' => $this->paymen_method,
            'payment_status' => $this->payment_status,
            'net_amount' => $this->net_amount,
            'tax_amount' => $this->tax_amount,
            'retention_amount' => $this->retention_amount,
            'total_purchase' => $this->total_purchase,
            'employee_id' => $this->employee_id,
            'status_purchase' => $this->status_purchase,
        ];
    }
}
