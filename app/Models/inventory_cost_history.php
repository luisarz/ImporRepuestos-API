<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class inventory_cost_history extends Model
{
    use HasFactory;

    protected $table = 'inventory_cost_histories';

    protected $fillable = [
        'inventory_id',
        'date',
        'before_cost',
        'actual_cost',
    ];

    protected $casts = [
        'date' => 'date',
        'before_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    // Relación con Inventory
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    // Accessor para old_cost (alias de before_cost)
    public function getOldCostAttribute()
    {
        return $this->before_cost;
    }

    // Accessor para new_cost (alias de actual_cost)
    public function getNewCostAttribute()
    {
        return $this->actual_cost;
    }

    // Scope para filtrar por rango de fechas
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Scope para filtrar por producto
    public function scopeByProduct($query, $productId)
    {
        return $query->whereHas('inventory', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }

    // Scope para filtrar por inventario
    public function scopeByInventory($query, $inventoryId)
    {
        return $query->where('inventory_id', $inventoryId);
    }
}
