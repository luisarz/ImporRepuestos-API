<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_type',
        'internal_code',
        'document_type_id',
        'document_number',
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
        'credit_limit' => 'decimal',
        'credit_amount' => 'decimal',
        'is_delivery' => 'boolean',
    ];

    public function internalCode(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CustomerDocumentsType::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
