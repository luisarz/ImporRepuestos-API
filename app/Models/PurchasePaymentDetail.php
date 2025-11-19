<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'payment_method_id',
        'casher_id',
        'payment_amount',
        'actual_balance',
        'bank_account_id',
        'reference',
        'is_active'
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Relación con la compra
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchasesHeader::class, 'purchase_id');
    }

    /**
     * Relación con el método de pago
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relación con el cajero/empleado que registró el pago
     */
    public function casher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'casher_id');
    }
}
