<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoriesBatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_inventory',
        'id_batch',
        'quantity',
        'operation_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_inventory' => 'integer',
        'id_batch' => 'integer',
        'quantity' => 'float',
        'operation_date' => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class,'id_inventory','id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class,'id_batch','id');
    }
}
