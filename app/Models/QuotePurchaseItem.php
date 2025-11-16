<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePurchaseItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_purchase_id',
        'inventory_id',
        'quantity',
        'price',
        'discount',
        'total',
        'is_compared',
        'is_purchased',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'quote_purchase_id' => 'integer',
        'inventory_id' => 'integer',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'is_compared' => 'integer',
        'is_purchased' => 'boolean',
    ];

    public function quotePurchase(): BelongsTo
    {
        return $this->belongsTo(QuotePurchase::class, 'quote_purchase_id');
    }

    public function inventory(): BelongsTo
    {
        // Corregido: debe apuntar a Inventory, no a Warehouse
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
