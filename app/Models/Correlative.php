<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correlative extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'document_type',
        'prefix',
        'current_number',
        'start_number',
        'padding_length',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'warehouse_id' => 'integer',
        'current_number' => 'integer',
        'start_number' => 'integer',
        'padding_length' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con Warehouse (Sucursal)
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Generar el próximo número correlativo formateado
     */
    public function generateNext(): string
    {
        $nextNumber = $this->current_number + 1;
        $paddedNumber = str_pad($nextNumber, $this->padding_length, '0', STR_PAD_LEFT);
        return $this->prefix . $paddedNumber;
    }

    /**
     * Obtener el número actual formateado (sin incrementar)
     */
    public function getCurrentFormatted(): string
    {
        $paddedNumber = str_pad($this->current_number, $this->padding_length, '0', STR_PAD_LEFT);
        return $this->prefix . $paddedNumber;
    }

    /**
     * Incrementar el correlativo
     */
    public function increment(): bool
    {
        $this->current_number += 1;
        return $this->save();
    }

    /**
     * Resetear el correlativo al número inicial
     */
    public function reset(): bool
    {
        $this->current_number = $this->start_number;
        return $this->save();
    }

    /**
     * Scope para obtener correlativos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por sucursal
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope para filtrar por tipo de documento
     */
    public function scopeByDocumentType($query, $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Obtener el correlativo activo para una sucursal y tipo de documento
     */
    public static function getActiveCorrelative($warehouseId, $documentType)
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->first();
    }
}
