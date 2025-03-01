<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesDteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'is_dte' => $this->is_dte,
            'generation_code' => $this->generation_code,
            'billing_model' => $this->billing_model,
            'transmition_type' => $this->transmition_type,
            'receipt_stamp' => $this->receipt_stamp,
            'json_url' => $this->json_url,
            'pdf_url' => $this->pdf_url,
        ];
    }
}
