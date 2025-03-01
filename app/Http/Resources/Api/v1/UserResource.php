<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'email_verifed_at' => $this->email_verifed_at,
            'rememeber_tokend' => $this->rememeber_tokend,
            'theme' => $this->theme,
            'teheme_color' => $this->teheme_color,
        ];
    }
}
