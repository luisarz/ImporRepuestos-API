<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'cost_without_tax',
        'cost_with_tax',
        'stock_actual',
        'stock_min',
        'alert_stock_min',
        'stock_max',
        'alert_stock_max',
        'max_discount',
        'last_purchase',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'warehouse_id' => 'integer',
        'product_id' => 'integer',
        'cost_without_tax' => 'decimal',
        'cost_with_tax' => 'decimal',
        'stock_actual' => 'decimal',
        'stock_min' => 'decimal',
        'alert_stock_min' => 'boolean',
        'stock_max' => 'decimal',
        'alert_stock_max' => 'boolean',
        'max_discount' => 'decimal',
        'last_purchase' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
