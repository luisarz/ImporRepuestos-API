<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'legal_name',
        'comercial_name',
        'document_type_id',
        'document_number',
        'economic_activity_id',
        'provider_type_id',
        'payment_type_id',
        'credit_days',
        'credit_limit',
        'debit_balance',
        'last_purchase',
        'decimal_purchase',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'document_type_id' => 'integer',
        'economic_activity_id' => 'integer',
        'provider_type_id' => 'integer',
        'payment_type_id' => 'integer',
        'credit_limit' => 'decimal',
        'debit_balance' => 'decimal',
        'last_purchase' => 'date',
        'is_active' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentsTypesProvider::class);
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivity::class);
    }

    public function providerType(): BelongsTo
    {
        return $this->belongsTo(ProvidersType::class);
    }
}
