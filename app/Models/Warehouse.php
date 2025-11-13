<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'stablishment_type_id',
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
        'establishment_type_code',
        'pos_terminal_code',
        'is_active'
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stablishmentType(): BelongsTo
    {
        return $this->belongsTo(StablishmentType::class,'stablishment_type_id','id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class);
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }
}
