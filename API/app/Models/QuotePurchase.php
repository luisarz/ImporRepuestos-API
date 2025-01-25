<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePurchase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_method',
        'provider',
        'date',
        'amount_purchase',
        'is_active',
        'is_purchased',
        'is_compared',
        'buyer_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'payment_method' => 'integer',
        'provider' => 'integer',
        'date' => 'date',
        'amount_purchase' => 'decimal',
        'is_active' => 'boolean',
        'is_purchased' => 'boolean',
        'is_compared' => 'boolean',
        'buyer_id' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
