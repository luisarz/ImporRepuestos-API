<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'inventory_id',
        'batch_id',
        'saled',
        'quantity',
        'price',
        'discount',
        'discount_percentage',
        'total',
        'is_saled',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'sale_id' => 'integer',
        'inventory_id' => 'integer',
        'batch_id' => 'integer',
        'saled' => 'boolean',
        'quantity' => 'float',
        'price' => 'float',
        'discount' => 'float',
        'discount_percentage' => 'float',
        'total' => 'float',
        'is_saled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SalesHeader::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoriesBatch::class);
    }
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class,'inventory_id','id');
    }
}
