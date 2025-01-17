<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasesHeader extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse',
        'provider_id',
        'purchcase_date',
        'serie',
        'purchase_number',
        'resolution',
        'purchase_type',
        'payment_method',
        'payment_status',
        'net_amount',
        'tax_amount',
        'retention_amount',
        'total_purchase',
        'employee_id',
        'status_purchase',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'warehouse' => 'integer',
        'provider_id' => 'integer',
        'purchcase_date' => 'date',
        'purchase_type' => 'integer',
        'net_amount' => 'decimal',
        'tax_amount' => 'decimal',
        'retention_amount' => 'decimal',
        'total_purchase' => 'decimal',
        'employee_id' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
