<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePaymentDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'cash_opening_id',
        'payment_method_id',
        'casher_id',
        'payment_amount',
        'actual_balance',
        'bank_account_id',
        'reference',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'sale_id' => 'integer',
        'cash_opening_id' => 'integer',
        'payment_method_id' => 'integer',
        'casher_id' => 'integer',
        'payment_amount' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'bank_account_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SalesHeader::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function casher(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function cashOpening(): BelongsTo
    {
        return $this->belongsTo(CashOpening::class);
    }
}
