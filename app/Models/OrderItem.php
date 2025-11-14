<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo OrderItem - Apunta a la tabla sale_items
 * Se usa para manejar items de órdenes (ventas pendientes/no facturadas)
 * Diferenciado del modelo SaleItem para separar lógica de negocio
 */
class OrderItem extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'sale_items';

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
        'discount', // Porcentaje de descuento (0-25)
        'total',
        'observations',
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
        'discount' => 'integer', // Porcentaje (0-25)
        'total' => 'float',
        'is_saled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con Order (orden padre)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sale_id');
    }

    /**
     * Relación con InventoriesBatch (lote)
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoriesBatch::class, 'batch_id');
    }

    /**
     * Relación con Inventory (inventario)
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    /**
     * Relación con Product (producto) a través de inventory
     * Acceso directo al producto del item
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            Inventory::class,
            'id', // Foreign key on inventory table
            'id', // Foreign key on products table
            'inventory_id', // Local key on order_items table
            'product_id' // Local key on inventory table
        );
    }
}
