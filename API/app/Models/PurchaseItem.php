<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_id',
        'batch_id',
        'is_purched',
        'quantity',
        'price',
        'discount',
        'total',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'purchase_id' => 'integer',
        'batch_id' => 'integer',
        'is_purched' => 'boolean',
        'quantity' => 'decimal',
        'price' => 'decimal',
        'discount' => 'decimal',
        'total' => 'decimal',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PurchasesHeader::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
