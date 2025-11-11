<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAddressCatalog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'district_id',
        'address_reference',
        'email',
        'phone',
        'seller',
        'seller_phone',
        'seller_email',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'provider_id'=> 'integer',
        'district_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the provider that owns the address.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
