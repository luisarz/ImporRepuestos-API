<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesDte extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'is_dte',
        'generation_code',
        'billing_model',
        'transmision_type',
        'receipt_stamp',
        'json_url',
        'pdf_url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'sale_id' => 'integer',
        'is_dte' => 'boolean',
        'generation_code' => 'integer',
        'billing_model' => 'integer',
        'transmision_type' => 'integer',
        'receipt_stamp' => 'integer',
        'json_url' => 'integer',
        'pdf_url' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SalesHeader::class);
    }
}
