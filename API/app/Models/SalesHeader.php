<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesHeader extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cashbox_open_id',
        'sale_date',
        'warehouse_id',
        'document_type_id',
        'document_internal_number',
        'seller_id',
        'customer_id',
        'operation_condition_id',
        'sale_status',
        'have_retention',
        'net_amount',
        'taxe',
        'discount',
        'retention',
        'sale_total',
        'payment_status',
        'is_order',
        'is_order_closed_without_invoiced',
        'is_invoiced_order',
        'discount_percentage',
        'discount_money',
        'total_order_after_discount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cashbox_open_id' => 'integer',
        'sale_date' => 'datetime',
        'warehouse_id' => 'integer',
        'document_type_id' => 'integer',
        'document_internal_number' => 'integer',
        'seller_id' => 'integer',
        'customer_id' => 'integer',
        'operation_condition_id' => 'integer',
        'have_retention' => 'boolean',
        'net_amount' => 'decimal',
        'taxe' => 'decimal',
        'discount' => 'decimal',
        'retention' => 'decimal',
        'sale_total' => 'decimal',
        'payment_status' => 'integer',
        'is_order' => 'boolean',
        'is_order_closed_without_invoiced' => 'boolean',
        'is_invoiced_order' => 'boolean',
        'discount_percentage' => 'decimal',
        'discount_money' => 'decimal',
        'total_order_after_discount' => 'decimal',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
