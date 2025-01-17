<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand_id',
        'model_id',
        'model_two',
        'year',
        'chassis',
        'vin',
        'motor',
        'displacement',
        'motor_type',
        'fuel_type',
        'vehicle_class',
        'income_date',
        'municipality_id',
        'antique',
        'plate_type',
        'capacity',
        'tonnage',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'brand_id' => 'integer',
        'model_id' => 'integer',
        'fuel_type' => 'integer',
        'income_date' => 'date',
        'municipality_id' => 'integer',
        'plate_type' => 'integer',
        'capacity' => 'decimal',
        'tonnage' => 'decimal',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function plateType(): BelongsTo
    {
        return $this->belongsTo(PlateType::class);
    }
}
