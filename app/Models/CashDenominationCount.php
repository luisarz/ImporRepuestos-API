<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDenominationCount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cash_denomination_counts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cash_opening_id',
        'denomination',
        'quantity',
        'total',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'denomination' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con CashOpening
     */
    public function cashOpening(): BelongsTo
    {
        return $this->belongsTo(CashOpening::class);
    }
}
