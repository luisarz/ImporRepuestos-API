<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'customer_type_id',
        'document_type_id',
        'document_number',
        'economic_activity_id',
        'country_id',
        'departament_id',
        'municipality_id',
        'phone',
        'email',
        'name',
        'last_name',
        'warehouse',
        'nrc',
        'nit',
        'is_exempt',
        'sales_type',
        'is_creditable',
        'address',
        'credit_limit',
        'credit_amount',
        'is_delivery',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'customer_type' => 'integer',
        'document_type_id' => 'integer',
        'warehouse' => 'integer',
        'is_exempt' => 'boolean',
        'is_creditable' => 'boolean',
        'credit_limit' => 'float',
        'credit_amount' => 'float',
        'is_delivery' => 'boolean',
    ];

    public function internalCode(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CustomerDocumentsType::class, 'document_type_id', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class,'warehouse','id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'departament_id', 'id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id', 'id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddressCatalog::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id', 'id');
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class, 'economic_activity_id', 'id');

    }

    public function documentTypeCustomer(): BelongsTo
    {
        return $this->belongsTo(CustomerDocumentsType::class, 'document_type_id', 'id');
    }
}
