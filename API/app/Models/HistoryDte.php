<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryDte extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_dte_id',
        'version',
        'ambiente',
        'status',
        'code_geneneration',
        'receipt_stamp',
        'fhProcesamiento',
        'clasifica_msg',
        'code_ms',
        'descripctionMsg',
        'observaciones',
        'dte',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'sale_dte_id' => 'integer',
    ];

    public function saleDte(): BelongsTo
    {
        return $this->belongsTo(SalesDte::class);
    }
}
