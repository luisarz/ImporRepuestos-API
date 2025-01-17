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
        'internal_code',
        'document_type_id',
        'document_number',
        'name',
        'last_name',
        'warehouse',
        'nrc',
        'nit',
        'is_taxed',
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
        'document_type_id' => 'integer',
        'warehouse' => 'integer',
        'is_taxed' => 'boolean',
        'is_creditable' => 'boolean',
        'credit_limit' => 'integer',
        'credit_amount' => 'decimal',
        'is_delivery' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CustomerDocumentsType::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
