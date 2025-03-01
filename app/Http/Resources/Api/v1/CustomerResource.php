<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_type' => $this->customer_type,
            'internal_code' => $this->internal_code,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->document_number,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'warehouse' => $this->warehouse,
            'nrc' => $this->nrc,
            'nit' => $this->nit,
            'is_exempt' => $this->is_exempt,
            'sales_type' => $this->sales_type,
            'is_creditable' => $this->is_creditable,
            'address' => $this->address,
            'credit_limit' => $this->credit_limit,
            'credit_amount' => $this->credit_amount,
            'is_delivery' => $this->is_delivery,
        ];
    }
}
