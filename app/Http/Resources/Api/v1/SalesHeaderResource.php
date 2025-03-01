<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesHeaderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cashbox_open_id' => $this->cashbox_open_id,
            'sale_date' => $this->sale_date,
            'warehouse_id' => $this->warehouse_id,
            'document_type_id' => $this->document_type_id,
            'document_internal_number' => $this->document_internal_number,
            'seller_id' => $this->seller_id,
            'customer_id' => $this->customer_id,
            'operation_condition_id' => $this->operation_condition_id,
            'sale_status' => $this->sale_status,
            'net_amount' => $this->net_amount,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'have_retention' => $this->have_retention,
            'retention' => $this->retention,
            'sale_total' => $this->sale_total,
            'payment_status' => $this->payment_status,
            'is_order' => $this->is_order,
            'is_order_closed_without_invoiced' => $this->is_order_closed_without_invoiced,
            'is_invoiced_order' => $this->is_invoiced_order,
            'discount_percentage' => $this->discount_percentage,
            'discount_money' => $this->discount_money,
            'total_order_after_discount' => $this->total_order_after_discount,
            'is_active' => $this->is_active,
        ];
    }
}
