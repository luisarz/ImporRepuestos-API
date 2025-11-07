<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de crear un nuevo precio
        static::creating(function ($price) {
            if ($price->is_default) {
                static::unsetOtherDefaults($price->inventory_id);
            }
        });

        // Antes de actualizar un precio existente
        static::updating(function ($price) {
            if ($price->is_default && $price->isDirty('is_default')) {
                static::unsetOtherDefaults($price->inventory_id, $price->id);
            }
        });
    }

    /**
     * Desactiva todos los demás precios predeterminados para el mismo inventario
     *
     * @param int $inventoryId
     * @param int|null $exceptPriceId ID del precio actual a excluir
     */
    protected static function unsetOtherDefaults(int $inventoryId, ?int $exceptPriceId = null)
    {
        $query = static::where('inventory_id', $inventoryId)
            ->where('is_default', true);

        if ($exceptPriceId) {
            $query->where('id', '!=', $exceptPriceId);
        }

        $query->update(['is_default' => false]);
    }

    protected $fillable = [
        'inventory_id',
        'price_description',
        'price',
        'utility',
        'is_default',
        'max_discount',
        'is_active',
        'quantity',
    ];


    protected $casts = [
        'id' => 'integer',
        'inventory_id' => 'integer',
        'price' => 'float',
        'max_discount' => 'float',
        'utility' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'quantity' => 'float',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
