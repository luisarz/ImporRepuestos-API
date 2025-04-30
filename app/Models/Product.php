<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'original_code',
        'barcode',
        'description',
        'brand_id',
        'category_id',
        'provider_id',
        'unit_measurement_id',
        'description_measurement_id',
        'image',
        'is_active',
        'is_taxed',
        'is_service',
        'is_temp',
        'is_discontinued',
        'is_not_purchasable',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'brand_id' => 'integer',
        'category_id' => 'integer',
        'provider_id' => 'integer',
        'unit_measurement_id' => 'integer',
        'is_active' => 'boolean',
        'is_taxed' => 'boolean',
        'is_service' => 'boolean',
        'is_temp'=>'boolean',
        'is_discontinued' => 'boolean',
        'is_not_purchasable' => 'boolean',
    ];
    // Accesor para obtener la URL completa de la imagen
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    // Eliminar la imagen física al eliminar el producto
    protected static function booted()
    {
        static::deleting(function ($product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
        });
        static::creating(function ($product) {
            // Marcar como temporal si no se especifica lo contrario
            if (!isset($product->is_temp)) {
                $product->is_temp = true;
            }

        });
        static::updating(function ($product) {
            // Marcar como temporal si no se especifica lo contrario
            if (isset($product->is_temp)) {
                $product->is_temp = false;
            }

        });

    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function unitMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitMeasurement::class);
    }
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id', 'id');
    }
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Application::class,'product_id','id');
    }
    public function equivalents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Equivalent::class,'product_id','id');
    }

}
