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
        'cash_register_id',
        'document_type_id',
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
        'cash_register_id' => 'integer',
        'document_type_id' => 'integer',
        'current_number' => 'integer',
        'start_number' => 'integer',
        'padding_length' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con CashRegister (Caja Registradora)
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Relación con DocumentType (Tipo de Documento DTE)
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
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
    public function incrementCorrelative(): bool
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
     * Scope para filtrar por caja registradora
     */
    public function scopeByCashRegister($query, $cashRegisterId)
    {
        return $query->where('cash_register_id', $cashRegisterId);
    }

    /**
     * Scope para filtrar por tipo de documento
     */
    public function scopeByDocumentType($query, $documentTypeId)
    {
        return $query->where('document_type_id', $documentTypeId);
    }

    /**
     * Obtener el correlativo activo para una caja registradora y tipo de documento
     */
    public static function getActiveCorrelative($cashRegisterId, $documentTypeId)
    {
        return self::where('cash_register_id', $cashRegisterId)
            ->where('document_type_id', $documentTypeId)
            ->where('is_active', true)
            ->first();
    }
}
