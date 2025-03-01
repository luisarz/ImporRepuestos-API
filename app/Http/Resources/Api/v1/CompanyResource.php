<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'district_id' => $this->district_id,
            'economic_activity_id' => $this->economic_activity_id,
            'company_name' => $this->company_name,
            'nrc' => $this->nrc,
            'nit' => $this->nit,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'address' => $this->address,
            'web' => $this->web,
            'api_key_mh' => $this->api_key_mh,
            'logo' => $this->logo,
            'is_active' => $this->is_active,
        ];
    }
}
