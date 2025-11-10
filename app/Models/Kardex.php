<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kardex extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'date',
        'operation_type',
        'operation_id',
        'operation_detail_id',
        'document_type',
        'document_number',
        'entity',
        'nationality',
        'inventory_id',
        'previous_stock',
        'stock_in',
        'stock_out',
        'stock_actual',
        'money_in',
        'money_out',
        'money_actual',
        'sale_price',
        'purchase_price',
        'promedial_cost',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'date' => 'datetime',
        'operation_id' => 'integer',
        'operation_detail_id' => 'integer',
        'inventory_id' => 'integer',
        'previous_stock' => 'decimal:2',
        'stock_in' => 'decimal:2',
        'stock_out' => 'decimal:2',
        'stock_actual' => 'decimal:2',
        'money_in' => 'decimal:2',
        'money_out' => 'decimal:2',
        'money_actual' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'promedial_cost' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'branch_id', 'id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}