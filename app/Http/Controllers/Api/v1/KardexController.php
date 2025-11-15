<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Kardex;
use App\Models\Branch;
use App\Exports\KardexExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KardexController extends Controller
{
    /**
     * Display a listing of the kardex with pagination.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'date');
            $sortOrder = $request->input('sortOrder', 'desc');

            // Filtros adicionales
            $movementType = $request->input('movement_type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $productId = $request->input('product_id');
            $warehouseId = $request->input('warehouse_id');

            $query = Kardex::with([
                'warehouse',
                'inventory.product',
                'inventoryBatch.batch'
            ]);

            // Aplicar búsqueda
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_number', 'like', "%{$search}%")
                      ->orWhere('document_type', 'like', "%{$search}%")
                      ->orWhere('entity', 'like', "%{$search}%")
                      ->orWhereHas('inventory.product', function ($pq) use ($search) {
                          $pq->where('description', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por tipo de movimiento
            if ($movementType) {
                if ($movementType === 'entry') {
                    $query->where('stock_in', '>', 0);
                } elseif ($movementType === 'exit') {
                    $query->where('stock_out', '>', 0);
                } elseif ($movementType === 'adjustment') {
                    $query->where('operation_type', 'like', '%ajuste%');
                }
            }

            // Filtro por rango de fechas (incluye todo el día)
            if ($startDate && $endDate) {
                $startDateTime = Carbon::parse($startDate)->startOfDay();
                $endDateTime = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('date', [$startDateTime, $endDateTime]);
            }

            // Filtro por producto
            if ($productId) {
                $query->whereHas('inventory', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            // Filtro por almacén
            if ($warehouseId) {
                $query->where('branch_id', $warehouseId);
            }

            // Aplicar ordenamiento
            if ($sortField && $sortField !== 'null') {
                $query->orderBy($sortField, $sortOrder ?: 'asc');
            } else {
                $query->orderBy('date', 'asc')->orderBy('id', 'asc');
            }

            $kardex = $query->paginate($perPage);

            // Transformar datos para el frontend
            $kardex->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'document_type' => $item->document_type,
                    'document_number' => $item->document_number,
                    'product' => $item->inventory?->product ? [
                        'id' => $item->inventory->product->id,
                        'code' => $item->inventory->product->code,
                        'description' => $item->inventory->product->description,
                    ] : null,
                    'product_name' => $item->inventory?->product?->description ?? 'N/A',
                    'movement_type' => $this->getMovementType($item),
                    'quantity' => $item->stock_in > 0 ? $item->stock_in : $item->stock_out,
                    'balance' => $item->stock_actual,
                    'cost' => $item->promedial_cost,
                    'warehouse' => $item->warehouse ? [
                        'id' => $item->warehouse->id,
                        'name' => $item->warehouse->name,
                    ] : null,
                    'warehouse_name' => $item->warehouse?->name ?? 'N/A',
                    'batch' => $item->inventoryBatch?->batch ? [
                        'id' => $item->inventoryBatch->batch->id,
                        'code' => $item->inventoryBatch->batch->code,
                    ] : null,
                    'batch_code' => $item->inventoryBatch?->batch?->code ?? null,
                    'operation_type' => $item->operation_type,
                    'entity' => $item->entity,
                    'stock_in' => $item->stock_in,
                    'stock_out' => $item->stock_out,
                    'previous_stock' => $item->previous_stock,
                    'purchase_price' => $item->purchase_price,
                    'sale_price' => $item->sale_price,
                ];
            });

            return ApiResponse::success($kardex, 'Kardex obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener kardex: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get statistics for the kardex.
     */
    public function getStats(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $movementType = $request->input('movement_type');
            $warehouseId = $request->input('warehouse_id');

            // Query base solo con filtro de fecha
            $baseQuery = Kardex::query();

            // Filtro por rango de fechas (incluye todo el día)
            if ($startDate && $endDate) {
                $startDateTime = Carbon::parse($startDate)->startOfDay();
                $endDateTime = Carbon::parse($endDate)->endOfDay();
                $baseQuery->whereBetween('date', [$startDateTime, $endDateTime]);
            }

            // Filtro por almacén
            if ($warehouseId) {
                $baseQuery->where('branch_id', $warehouseId);
            }

            // Calcular estadísticas
            $stats = [
                'total_movements' => (clone $baseQuery)->count(),
                'entries' => (clone $baseQuery)->where('stock_in', '>', 0)->count(),
                'exits' => (clone $baseQuery)->where('stock_out', '>', 0)->count(),
                'adjustments' => (clone $baseQuery)->where('operation_type', 'like', '%ajuste%')->count(),
                'total_stock_in' => (clone $baseQuery)->sum('stock_in'),
                'total_stock_out' => (clone $baseQuery)->sum('stock_out'),
                'total_money_in' => (clone $baseQuery)->sum('money_in'),
                'total_money_out' => (clone $baseQuery)->sum('money_out'),
            ];

            // Si hay filtro de tipo de movimiento, ajustar el total_movements
            if ($movementType) {
                $filteredQuery = clone $baseQuery;
                if ($movementType === 'entry') {
                    $stats['total_movements'] = $filteredQuery->where('stock_in', '>', 0)->count();
                } elseif ($movementType === 'exit') {
                    $stats['total_movements'] = $filteredQuery->where('stock_out', '>', 0)->count();
                } elseif ($movementType === 'adjustment') {
                    $stats['total_movements'] = $filteredQuery->where('operation_type', 'like', '%ajuste%')->count();
                }
            }

            return ApiResponse::success($stats, 'Estadísticas obtenidas correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get kardex by product ID.
     */
    public function getByProduct($productId)
    {
        try {
            $kardex = Kardex::with([
                'warehouse',
                'inventory.product',
                'inventoryBatch'
            ])
            ->whereHas('inventory', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            return ApiResponse::success($kardex, 'Kardex del producto obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener kardex del producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get kardex by warehouse ID.
     */
    public function getByWarehouse($warehouseId)
    {
        try {
            $kardex = Kardex::with([
                'warehouse',
                'inventory.product',
                'inventoryBatch'
            ])
            ->where('branch_id', $warehouseId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            return ApiResponse::success($kardex, 'Kardex del almacén obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener kardex del almacén: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get kardex by date range.
     */
    public function getByDateRange(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if (!$startDate || !$endDate) {
                return ApiResponse::error('Se requieren las fechas de inicio y fin', 400);
            }

            // Agregar hora para incluir todo el día
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();

            $kardex = Kardex::with([
                'warehouse',
                'inventory.product',
                'inventoryBatch'
            ])
            ->whereBetween('date', [$startDateTime, $endDateTime])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            return ApiResponse::success($kardex, 'Kardex por rango de fechas obtenido correctamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener kardex por fechas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Export kardex report to PDF.
     */
    public function exportReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $movementType = $request->input('movement_type');
            $productId = $request->input('product_id');
            $warehouseId = $request->input('warehouse_id');

            $query = Kardex::with([
                'warehouse',
                'inventory.product',
                'inventoryBatch.batch'
            ]);

            // Aplicar filtros de fecha (incluye todo el día)
            if ($startDate && $endDate) {
                $startDateTime = Carbon::parse($startDate)->startOfDay();
                $endDateTime = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('date', [$startDateTime, $endDateTime]);
            }

            if ($movementType) {
                if ($movementType === 'entry') {
                    $query->where('stock_in', '>', 0);
                } elseif ($movementType === 'exit') {
                    $query->where('stock_out', '>', 0);
                } elseif ($movementType === 'adjustment') {
                    $query->where('operation_type', 'like', '%ajuste%');
                }
            }

            if ($productId) {
                $query->whereHas('inventory', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if ($warehouseId) {
                $query->where('branch_id', $warehouseId);
            }

            $kardex = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

            // Generar PDF
            $warehouse = $warehouseId ? Branch::find($warehouseId)?->name : null;

            $pdf = \PDF::loadView('reports.kardex', [
                'kardex' => $kardex,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'warehouse' => $warehouse,
                'movementType' => $movementType,
                'date' => now()->format('d/m/Y H:i')
            ]);

            return $pdf->stream('kardex_' . time() . '.pdf');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al generar reporte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get movement type from kardex item.
     */
    private function getMovementType($item)
    {
        if ($item->stock_in > 0) {
            return 'entry';
        } elseif ($item->stock_out > 0) {
            return 'exit';
        } else {
            return 'adjustment';
        }
    }
}
