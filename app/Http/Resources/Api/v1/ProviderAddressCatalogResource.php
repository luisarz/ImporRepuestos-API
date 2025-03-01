<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderAddressCatalogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'district_id' => $this->district_id,
            'address_reference' => $this->address_reference,
            'email' => $this->email,
            'phone' => $this->phone,
            'seller' => $this->seller,
            'seller_phone' => $this->seller_phone,
            'seller_email' => $this->seller_email,
            'is_active' => $this->is_active,
        ];
    }
}
