<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'job_title_id' => $this->job_title_id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'dui' => $this->dui,
            'nit' => $this->nit,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'district_id' => $this->district_id,
            'address' => $this->address,
            'comision_porcentage' => $this->comision_porcentage,
            'is_active' => $this->is_active,
            'marital_status' => $this->marital_status,
            'marital_name' => $this->marital_name,
            'marital_phone' => $this->marital_phone,
        ];
    }
}
