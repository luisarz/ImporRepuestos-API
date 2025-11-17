<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'transfer_date',
        'warehouse_origin_id',
        'warehouse_destination_id',
        'status',
        'sent_by',
        'received_by',
        'sent_at',
        'received_at',
        'observations',
    ];

    protected $casts = [
        'id' => 'integer',
        'warehouse_origin_id' => 'integer',
        'warehouse_destination_id' => 'integer',
        'sent_by' => 'integer',
        'received_by' => 'integer',
        'transfer_date' => 'date',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($transfer) {
            if (!$transfer->transfer_number) {
                // Generar número de traslado automático
                $lastTransfer = Transfer::orderBy('id', 'desc')->first();
                $nextNumber = $lastTransfer ? intval(substr($lastTransfer->transfer_number, 6)) + 1 : 1;
                $transfer->transfer_number = 'TRANS-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            }
        });
    }

    public function warehouseOrigin(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_origin_id');
    }

    public function warehouseDestination(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_destination_id');
    }

    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }
}
