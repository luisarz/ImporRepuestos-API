<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DteAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DteAuditLogController extends Controller
{
    /**
     * Listar logs de auditoría con filtros y paginación
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $query = DteAuditLog::with(['salesHeader', 'user', 'resolvedByUser']);

            // Filtro por búsqueda general
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('error_message', 'like', "%{$search}%")
                      ->orWhere('error_code', 'like', "%{$search}%")
                      ->orWhere('generation_code', 'like', "%{$search}%")
                      ->orWhereHas('salesHeader', function($sq) use ($search) {
                          $sq->where('document_internal_number', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por estado
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Filtro por tipo de documento
            if ($request->has('document_type')) {
                $query->where('document_type', $request->input('document_type'));
            }

            // Filtro por acción
            if ($request->has('action')) {
                $query->where('action', $request->input('action'));
            }

            // Filtro por venta
            if ($request->has('sales_header_id')) {
                $query->where('sales_header_id', $request->input('sales_header_id'));
            }

            // Filtro por usuario
            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            // Filtro por fecha desde
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            // Filtro por fecha hasta
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Filtro solo no resueltos
            if ($request->has('unresolved') && $request->boolean('unresolved')) {
                $query->unresolved();
            }

            // Filtro solo fallos
            if ($request->has('only_failed') && $request->boolean('only_failed')) {
                $query->failed();
            }

            // Filtro solo rechazados
            if ($request->has('only_rejected') && $request->boolean('only_rejected')) {
                $query->rejected();
            }

            $auditLogs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success($auditLogs, 'Logs de auditoría recuperados', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un log específico
     */
    public function show(DteAuditLog $dteAuditLog): JsonResponse
    {
        try {
            $dteAuditLog->load(['salesHeader.customer', 'user', 'resolvedByUser']);
            return ApiResponse::success($dteAuditLog, 'Log de auditoría recuperado', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de los logs de auditoría
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total' => DteAuditLog::count(),
                'fallos' => DteAuditLog::failed()->count(),
                'rechazados' => DteAuditLog::rejected()->count(),
                'pendientes' => DteAuditLog::where('status', 'PENDIENTE')->count(),
                'no_resueltos' => DteAuditLog::unresolved()->count(),
                'resueltos' => DteAuditLog::whereNotNull('resolved_at')->count(),
                'ultimo_mes' => DteAuditLog::whereMonth('created_at', now()->month)
                                          ->whereYear('created_at', now()->year)
                                          ->count(),
                'por_tipo_documento' => DteAuditLog::selectRaw('document_type, COUNT(*) as total')
                                                    ->whereNotNull('document_type')
                                                    ->groupBy('document_type')
                                                    ->get(),
                'por_accion' => DteAuditLog::selectRaw('action, COUNT(*) as total')
                                           ->groupBy('action')
                                           ->get(),
                'errores_mas_frecuentes' => DteAuditLog::selectRaw('error_code, error_message, COUNT(*) as total')
                                                       ->whereNotNull('error_code')
                                                       ->groupBy('error_code', 'error_message')
                                                       ->orderByDesc('total')
                                                       ->limit(10)
                                                       ->get(),
            ];

            return ApiResponse::success($stats, 'Estadísticas de auditoría', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Marcar un log como resuelto
     */
    public function markAsResolved(Request $request, DteAuditLog $dteAuditLog): JsonResponse
    {
        try {
            $request->validate([
                'resolution_notes' => 'nullable|string|max:1000',
            ]);

            $dteAuditLog->markAsResolved($request->input('resolution_notes'));

            return ApiResponse::success($dteAuditLog, 'Log marcado como resuelto', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Incrementar el contador de reintentos
     */
    public function incrementRetry(DteAuditLog $dteAuditLog): JsonResponse
    {
        try {
            $dteAuditLog->incrementRetryCount();

            return ApiResponse::success($dteAuditLog, 'Contador de reintentos incrementado', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Obtener logs de auditoría por venta
     */
    public function bySale($saleId): JsonResponse
    {
        try {
            $auditLogs = DteAuditLog::bySale($saleId)
                                    ->with(['user', 'resolvedByUser'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

            return ApiResponse::success($auditLogs, 'Logs de auditoría de la venta', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar un log (soft delete)
     */
    public function destroy(DteAuditLog $dteAuditLog): JsonResponse
    {
        try {
            $dteAuditLog->delete();
            return ApiResponse::success(null, 'Log eliminado correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Limpiar logs antiguos o resueltos
     * Opciones:
     * - resolved: Eliminar logs resueltos
     * - days: Eliminar logs más antiguos de X días
     * - all_resolved: Forzar eliminación permanente de logs resueltos
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:resolved,old,all',
                'days' => 'nullable|integer|min:1',
                'permanent' => 'boolean',
            ]);

            $type = $request->input('type');
            $days = $request->input('days', 30);
            $permanent = $request->boolean('permanent', false);
            $deletedCount = 0;

            switch ($type) {
                case 'resolved':
                    // Eliminar logs resueltos
                    $query = DteAuditLog::whereNotNull('resolved_at');
                    if ($permanent) {
                        $deletedCount = $query->forceDelete();
                    } else {
                        $deletedCount = $query->delete();
                    }
                    $message = "Se eliminaron {$deletedCount} logs resueltos";
                    break;

                case 'old':
                    // Eliminar logs más antiguos de X días
                    $query = DteAuditLog::where('created_at', '<', now()->subDays($days));
                    if ($permanent) {
                        $deletedCount = $query->forceDelete();
                    } else {
                        $deletedCount = $query->delete();
                    }
                    $message = "Se eliminaron {$deletedCount} logs más antiguos de {$days} días";
                    break;

                case 'all':
                    // Eliminar TODOS los logs (requiere confirmación)
                    if (!$request->has('confirm') || !$request->boolean('confirm')) {
                        return ApiResponse::error(null, 'Se requiere confirmación para eliminar todos los logs', 400);
                    }
                    if ($permanent) {
                        $deletedCount = DteAuditLog::forceDelete();
                    } else {
                        $deletedCount = DteAuditLog::delete();
                    }
                    $message = "Se eliminaron TODOS los logs ({$deletedCount} registros)";
                    break;
            }

            return ApiResponse::success([
                'deleted_count' => $deletedCount,
                'type' => $type,
                'permanent' => $permanent,
            ], $message, 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }
}
