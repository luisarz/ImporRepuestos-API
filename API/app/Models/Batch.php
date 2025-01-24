<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'origen_code',
        'inventory_id',
        'incoming_date',
        'expiration_date',
        'initial_quantity',
        'available_quantity',
        'observations',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'origen_code' => 'integer',
        'inventory_id' => 'integer',
        'incoming_date' => 'date',
        'expiration_date' => 'date',
        'initial_quantity' => 'decimal',
        'available_quantity' => 'decimal',
        'is_active' => 'boolean',
    ];

    public function origenCode(): BelongsTo
    {
        return $this->belongsTo(BatchCodeOrigen::class);
    }
}
