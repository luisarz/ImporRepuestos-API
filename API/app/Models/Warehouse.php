<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'stablishment_type',
        'name',
        'nrc',
        'nit',
        'district_id',
        'economic_activity_id',
        'address',
        'phone',
        'email',
        'product_prices',
        'logo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'stablishment_type' => 'integer',
        'district_id' => 'integer',
        'economic_activity_id' => 'integer',
        'logo' => 'array',
    ];

    public function id(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stablishmentType(): BelongsTo
    {
        return $this->belongsTo(StablishmentType::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class);
    }
}
