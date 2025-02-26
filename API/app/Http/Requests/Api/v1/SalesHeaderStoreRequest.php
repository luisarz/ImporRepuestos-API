<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class SalesHeaderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cashbox_open_id' => ['required', 'integer'],
            'sale_date' => ['required'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'document_type_id' => ['required', 'integer'],
            'document_internal_number' => ['required', 'integer'],
            'seller_id' => ['required', 'integer', 'exists:employees,id'],
            'customer_id' => ['required', 'integer','exists:customers,id'],
            'operation_condition_id' => ['required', 'integer'],
            'sale_status' => ['required', 'in:1,2,3'],
            'net_amount' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'have_retention' => ['required'],
            'retention' => ['required', 'numeric'],
            'sale_total' => ['required', 'numeric'],
            'payment_status' => ['required', 'integer'],
            'is_order' => ['required'],
            'is_order_closed_without_invoiced' => ['required'],
            'is_invoiced_order' => ['required'],
            'discount_percentage' => ['required', 'numeric'],
            'discount_money' => ['required', 'numeric'],
            'total_order_after_discount' => ['required', 'numeric'],
            'is_active' => ['required'],
        ];
    }
}
