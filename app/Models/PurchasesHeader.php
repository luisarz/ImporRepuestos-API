<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'quote_purchase_id',
        'provider_id',
        'purchase_date',
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
        'quote_purchase_id' => 'integer',
        'provider_id' => 'integer',
        'purchase_date' => 'date',
        'purchase_type' => 'integer',
        'net_amount' => 'float',
        'tax_amount' => 'float',
        'retention_amount' => 'float',
        'total_purchase' => 'float',
        'employee_id' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class,'warehouse','id');
    }

    public function quotePurchase(): BelongsTo
    {
        return $this->belongsTo(QuotePurchase::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
