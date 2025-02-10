<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'last_cost_without_tax',
        'last_cost_with_tax',
        'stock_actual_quantity',
        'stock_min',
        'alert_stock_min',
        'stock_max',
        'alert_stock_max',
        'last_purchase',
        'is_service',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'warehouse_id' => 'integer',
        'last_cost_without_tax' => 'float',
        'last_cost_with_tax' => 'float',
        'stock_actual_quantity' => 'float',
        'stock_min' => 'float',
        'alert_stock_min' => 'boolean',
        'stock_max' => 'float',
        'alert_stock_max' => 'boolean',
        'last_purchase' => 'datetime',
        'is_service' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');

    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);

    }

        public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoriesBatch::class, 'id_inventory');
    }

}
