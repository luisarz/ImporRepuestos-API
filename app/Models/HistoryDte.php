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
        'sales_invoice_id',
        'version',
        'ambiente',
        'versionApp',
        'estado',
        'codigoGeneracion',
        'selloRecibido',
        'num_control',
        'fhProcesamiento',
        'clasificaMsg',
        'codigoMsg',
        'descripcionMsg',
        'observaciones',
        'dte',
        'contingencia',
        'motivo_contingencia'
    ];
    protected $casts = [
        'dte' => 'array',
    ];
    public function salesInvoice()
    {
        return $this->belongsTo(Sale::class,'sales_invoice_id','id');
    }
}
