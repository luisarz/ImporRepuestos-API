<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\inventory_cost_history;
use Illuminate\Http\Request;

class InventoryCostHistoryController extends Controller
{
    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'created_at');
            $sortOrder = $request->input('sortOrder', 'desc');

            // Filtros adicionales
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $productId = $request->input('product_id');
            $inventoryId = $request->input('inventory_id');

            $query = inventory_cost_history::with([
                'inventory.warehouse',
                'inventory.product'
            ]);

            // Aplicar búsqueda
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('inventory.product', function ($pq) use ($search) {
                        $pq->where('description', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('inventory.warehouse', function ($wq) use ($search) {
                        $wq->where('name', 'like', "%{$search}%");
                    });
                });
            }

            // Aplicar filtros
            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }

            if ($productId) {
                $query->whereHas('inventory', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if ($inventoryId) {
                $query->byInventory($inventoryId);
            }

            // Aplicar ordenamiento
            if ($sortField && $sortField !== 'null') {
                $query->orderBy($sortField, $sortOrder ?: 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $costHistory = $query->paginate($perPage);

            return ApiResponse::success($costHistory, 'Historial de costos obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener historial de costos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for the cost history.
     */
    public function getStats(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $query = inventory_cost_history::query();

            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }

            $stats = [
                'total_records' => $query->count(),
                'average_cost' => round($query->avg('actual_cost'), 2) ?? 0,
                'max_cost' => round($query->max('actual_cost'), 2) ?? 0,
                'min_cost' => round($query->min('actual_cost'), 2) ?? 0,
            ];

            return ApiResponse::success($stats, 'Estadísticas obtenidas correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get cost history by product ID.
     */
    public function getByProduct($productId)
    {
        try {
            $costHistory = inventory_cost_history::with([
                'inventory.warehouse',
                'inventory.product'
            ])
            ->whereHas('inventory', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

            return ApiResponse::success($costHistory, 'Historial de costos del producto obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener historial del producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get cost history by inventory ID.
     */
    public function getByInventory($inventoryId)
    {
        try {
            $costHistory = inventory_cost_history::with([
                'inventory.warehouse',
                'inventory.product'
            ])
            ->where('inventory_id', $inventoryId)
            ->orderBy('created_at', 'desc')
            ->get();

            return ApiResponse::success($costHistory, 'Historial de costos del inventario obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener historial del inventario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get cost history by date range.
     */
    public function getByDateRange(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if (!$startDate || !$endDate) {
                return ApiResponse::error('Se requieren las fechas de inicio y fin', 400);
            }

            $costHistory = inventory_cost_history::with([
                'inventory.warehouse',
                'inventory.product'
            ])
            ->byDateRange($startDate, $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

            return ApiResponse::success($costHistory, 'Historial de costos por rango de fechas obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener historial por fechas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export cost history report.
     */
    public function exportReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $productId = $request->input('product_id');
            $inventoryId = $request->input('inventory_id');

            $query = inventory_cost_history::with([
                'inventory.warehouse',
                'inventory.product'
            ]);

            // Aplicar filtros
            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }

            if ($productId) {
                $query->whereHas('inventory', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if ($inventoryId) {
                $query->byInventory($inventoryId);
            }

            $costHistory = $query->orderBy('created_at', 'desc')->get();

            return ApiResponse::success($costHistory, 'Datos para exportación obtenidos correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener datos para exportar: ' . $e->getMessage(), 500);
        }
    }
}
