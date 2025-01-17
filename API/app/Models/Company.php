<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'district_id',
        'economic_activity_id',
        'company_name',
        'nrc',
        'nit',
        'phone',
        'whatsapp',
        'email',
        'address',
        'web',
        'api_key_mh',
        'logo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'district_id' => 'integer',
        'economic_activity_id' => 'integer',
        'web' => 'integer',
        'api_key_mh' => 'integer',
        'logo' => 'array',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class);
    }
}
