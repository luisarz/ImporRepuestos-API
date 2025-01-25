<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryDteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_dte_id' => $this->sale_dte_id,
            'version' => $this->version,
            'ambiente' => $this->ambiente,
            'status' => $this->status,
            'code_generation' => $this->code_generation,
            'receipt_stamp' => $this->receipt_stamp,
            'fhProcesamiento' => $this->fhProcesamiento,
            'clasifica_msg' => $this->clasifica_msg,
            'code_mgs' => $this->code_mgs,
            'description_msg' => $this->description_msg,
            'observations' => $this->observations,
            'dte' => $this->dte,
        ];
    }
}
