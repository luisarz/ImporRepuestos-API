<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relación con Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accesor para obtener la URL completa de la imagen
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    /**
     * Eliminar la imagen física al eliminar el registro
     */
    protected static function booted(): void
    {
        static::deleting(function ($productImage) {
            if ($productImage->image_path) {
                Storage::disk('public')->delete($productImage->image_path);
            }
        });
    }
}
