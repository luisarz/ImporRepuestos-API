<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'image' => 'array',
        'is_active' => 'boolean',
        'is_taxed' => 'boolean',
        'is_service' => 'boolean',
    ];

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

}
