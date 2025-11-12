<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashRegisterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $warehouseFilter = $request->input('warehouse_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = CashRegister::query()->with('warehouse');

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Filtro por sucursal
            if ($warehouseFilter !== '') {
                $query->where('warehouse_id', $warehouseFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'code', 'name', 'warehouse_id', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $cashRegisters = $query->paginate($perPage);
            return ApiResponse::success($cashRegisters, 'Lista de cajas registradoras', 200);
        } catch (Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:cash_registers,code',
                'name' => 'required|string|max:100',
                'warehouse_id' => 'required|exists:warehouses,id',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $cashRegister = CashRegister::create($validated);
            $cashRegister->load('warehouse');

            return ApiResponse::success($cashRegister, 'Caja registradora creada exitosamente', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al crear la caja registradora', 400);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $cashRegister = CashRegister::with(['warehouse', 'currentOpening'])->findOrFail($id);
            return ApiResponse::success($cashRegister, 'Detalle de la caja registradora', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Caja registradora no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener la caja registradora', 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $cashRegister = CashRegister::findOrFail($id);

            $validated = $request->validate([
                'code' => ['sometimes', 'string', 'max:20', Rule::unique('cash_registers')->ignore($id)],
                'name' => 'sometimes|string|max:100',
                'warehouse_id' => 'sometimes|exists:warehouses,id',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $cashRegister->update($validated);
            $cashRegister->load('warehouse');

            return ApiResponse::success($cashRegister, 'Caja registradora actualizada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Caja registradora no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar la caja registradora', 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $cashRegister = CashRegister::findOrFail($id);

            // Verificar que no tenga aperturas
            if ($cashRegister->cashOpenings()->exists()) {
                return ApiResponse::error(null, 'No se puede eliminar una caja con aperturas registradas', 400);
            }

            $cashRegister->delete();
            return ApiResponse::success(null, 'Caja registradora eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Caja registradora no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar la caja registradora', 400);
        }
    }

    /**
     * Obtener todas las cajas activas (sin paginar)
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');

            $query = CashRegister::where('is_active', 1)
                ->select('id', 'code', 'name', 'warehouse_id')
                ->with('warehouse:id,name');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $cashRegisters = $query->orderBy('name')->get();
            return ApiResponse::success($cashRegisters, 'Cajas activas recuperadas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener las cajas', 500);
        }
    }

    /**
     * Estadísticas
     */
    public function stats(): JsonResponse
    {
        try {
            $total = CashRegister::count();
            $active = CashRegister::where('is_active', 1)->count();
            $inactive = CashRegister::where('is_active', 0)->count();
            $withOpenCash = CashRegister::whereHas('currentOpening')->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'with_open_cash' => $withOpenCash,
            ];

            return ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }

    /**
     * Acciones grupales
     */
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $cashRegisters = CashRegister::whereIn('id', $ids)
                ->with('warehouse')
                ->get();
            return ApiResponse::success($cashRegisters, 'Cajas recuperadas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener las cajas', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CashRegister::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Cajas activadas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al activar las cajas', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CashRegister::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Cajas desactivadas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al desactivar las cajas', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            // Verificar que ninguna tenga aperturas
            $withOpenings = CashRegister::whereIn('id', $ids)
                ->whereHas('cashOpenings')
                ->exists();

            if ($withOpenings) {
                return ApiResponse::error(null, 'Algunas cajas tienen aperturas registradas y no pueden ser eliminadas', 400);
            }

            CashRegister::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Cajas eliminadas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar las cajas', 500);
        }
    }
}
