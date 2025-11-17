<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'inventory_origin_id',
        'inventory_destination_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'transfer_id' => 'integer',
        'product_id' => 'integer',
        'inventory_origin_id' => 'integer',
        'inventory_destination_id' => 'integer',
        'batch_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryOrigin(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_origin_id');
    }

    public function inventoryDestination(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_destination_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
