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
            'warehouse' => $this->whenLoaded('warehouse'),
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->whenLoaded('documentType'),
            'document_internal_number' => $this->document_internal_number,
            'seller_id' => $this->seller_id,
            'seller' => $this->whenLoaded('seller'),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer'),
            'operation_condition_id' => $this->operation_condition_id,
            'operation_condition' => $this->whenLoaded('saleCondition'),
            'sale_status' => $this->sale_status,
            'net_amount' => $this->net_amount,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'have_retention' => $this->have_retention,
            'retention' => $this->retention,
            'sale_total' => $this->sale_total,
            'payment_method_id' => $this->payment_method_id,
            'payment_method' => $this->whenLoaded('paymentMethod'),
            'payment_status' => $this->payment_status,
            'pending_balance' => $this->pending_balance,
            'is_order' => $this->is_order,
            'is_order_closed_without_invoiced' => $this->is_order_closed_without_invoiced,
            'is_invoiced_order' => $this->is_invoiced_order,
            'discount_percentage' => $this->discount_percentage,
            'discount_money' => $this->discount_money,
            'total_order_after_discount' => $this->total_order_after_discount,
            'is_dte' => $this->is_dte,
            'generationCode' => $this->generationCode,
            'is_active' => $this->is_active,
            // Campos calculados para el frontend
            'sale_number_formatted' => $this->sale_number_formatted ?? 'Sin Asignar',
        ];
    }
}
