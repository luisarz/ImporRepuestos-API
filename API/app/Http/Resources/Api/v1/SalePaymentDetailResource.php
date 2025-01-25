<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalePaymentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'payment_method_id' => $this->payment_method_id,
            'casher_id' => $this->casher_id,
            'payment_amount' => $this->payment_amount,
            'actual_balance' => $this->actual_balance,
            'bank_account_id' => $this->bank_account_id,
            'reference' => $this->reference,
            'is_active' => $this->is_active,
        ];
    }
}
