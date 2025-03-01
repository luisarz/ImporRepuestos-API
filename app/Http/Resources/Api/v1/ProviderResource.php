<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProviderResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {


        return [
            'id' => $this->id,
            'legal_name' => $this->legal_name,
            'comercial_name' => $this->comercial_name,
            'document_type_id' => $this->document_type_id,
            'document_number' => $this->document_number,
            'economic_activity_id' => $this->economic_activity_id,
            'provider_type_id' => $this->provider_type_id,
            'payment_type_id' => $this->payment_type_id,
            'credit_days' => $this->credit_days,
            'credit_limit' =>  $this->credit_limit,
            'debit_balance' => $this->debit_balance,
            'last_purchase' => $this->last_purchase,
            'decimal_purchase' => $this->decimal_purchase,
            'is_active' => $this->is_active
        ];
    }


}
