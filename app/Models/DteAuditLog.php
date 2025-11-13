<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DteAuditLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_header_id',
        'user_id',
        'document_type',
        'action',
        'status',
        'error_code',
        'error_message',
        'request_data',
        'response_data',
        'stack_trace',
        'generation_code',
        'ip_address',
        'user_agent',
        'retry_count',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'resolved_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Relación con la venta
     */
    public function salesHeader(): BelongsTo
    {
        return $this->belongsTo(SalesHeader::class, 'sales_header_id');
    }

    /**
     * Relación con el usuario que intentó generar el DTE
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el usuario que resolvió el problema
     */
    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope para obtener solo errores no resueltos
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope para obtener solo errores de tipo FALLO
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'FALLO');
    }

    /**
     * Scope para obtener solo errores rechazados
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'RECHAZADO');
    }

    /**
     * Scope para filtrar por venta
     */
    public function scopeBySale($query, $saleId)
    {
        return $query->where('sales_header_id', $saleId);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Método estático para crear un log de auditoría de error
     * Si ya existe un log no resuelto con el mismo error exacto, incrementa el contador
     * Si el error es diferente, crea un nuevo registro
     */
    public static function logError(
        $salesHeaderId,
        $action,
        $errorMessage,
        $documentType = null,
        $errorCode = null,
        $requestData = null,
        $responseData = null,
        $stackTrace = null,
        $generationCode = null
    ) {
        // Buscar un log no resuelto existente con el MISMO error (mismo código Y mensaje)
        $existingLog = self::where('sales_header_id', $salesHeaderId)
            ->where('action', $action)
            ->where('status', 'FALLO')
            ->whereNull('resolved_at')
            ->where('error_code', $errorCode)
            ->where('error_message', $errorMessage)
            ->first();

        if ($existingLog) {
            // Si existe el MISMO error, solo incrementar contador y actualizar datos
            $existingLog->increment('retry_count');
            $existingLog->update([
                'request_data' => $requestData,
                'response_data' => $responseData,
                'stack_trace' => $stackTrace,
                'generation_code' => $generationCode,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'updated_at' => now(),
            ]);
            return $existingLog;
        }

        // Si es un error DIFERENTE o no existe, crear uno nuevo
        return self::create([
            'sales_header_id' => $salesHeaderId,
            'user_id' => auth()->id(),
            'document_type' => $documentType,
            'action' => $action,
            'status' => 'FALLO',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'stack_trace' => $stackTrace,
            'generation_code' => $generationCode,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'retry_count' => 0,
        ]);
    }

    /**
     * Método para registrar un rechazo (DTE generado pero rechazado por el MH)
     * Si ya existe un log no resuelto con el mismo error exacto, incrementa el contador
     * Si el error es diferente, crea un nuevo registro
     */
    public static function logRejected(
        $salesHeaderId,
        $action,
        $errorMessage,
        $documentType = null,
        $errorCode = null,
        $requestData = null,
        $responseData = null,
        $generationCode = null
    ) {
        // Buscar un log no resuelto existente con el MISMO rechazo (mismo código Y mensaje)
        $existingLog = self::where('sales_header_id', $salesHeaderId)
            ->where('action', $action)
            ->where('status', 'RECHAZADO')
            ->whereNull('resolved_at')
            ->where('error_code', $errorCode)
            ->where('error_message', $errorMessage)
            ->first();

        if ($existingLog) {
            // Si existe el MISMO rechazo, solo incrementar contador y actualizar datos
            $existingLog->increment('retry_count');
            $existingLog->update([
                'request_data' => $requestData,
                'response_data' => $responseData,
                'generation_code' => $generationCode,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'updated_at' => now(),
            ]);
            return $existingLog;
        }

        // Si es un rechazo DIFERENTE o no existe, crear uno nuevo
        return self::create([
            'sales_header_id' => $salesHeaderId,
            'user_id' => auth()->id(),
            'document_type' => $documentType,
            'action' => $action,
            'status' => 'RECHAZADO',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'generation_code' => $generationCode,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'retry_count' => 0,
        ]);
    }

    /**
     * Método para marcar como resuelto
     */
    public function markAsResolved($notes = null)
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Incrementar contador de reintentos
     */
    public function incrementRetryCount()
    {
        $this->increment('retry_count');
    }
}
