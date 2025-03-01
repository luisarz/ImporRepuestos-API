<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;


    protected $fillable = [
        'inventory_id',
        'price_description',
        'price',
        'max_discount',
        'is_active',
        'quantity',
    ];


    protected $casts = [
        'id' => 'integer',
        'inventory_id' => 'integer',
        'price' => 'float',
        'max_discount' => 'float',
        'is_active' => 'boolean',
        'quantity' => 'float',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
