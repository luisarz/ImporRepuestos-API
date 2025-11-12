<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Correlative;
use App\Services\CorrelativeService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CorrelativeController extends Controller
{
    protected $correlativeService;

    public function __construct(CorrelativeService $correlativeService)
    {
        $this->correlativeService = $correlativeService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $warehouseFilter = $request->input('warehouse_filter', '');
            $documentTypeFilter = $request->input('document_type_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = Correlative::query()->with('warehouse');

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('document_type', 'like', "%{$search}%")
                      ->orWhere('prefix', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtros
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            if ($warehouseFilter !== '') {
                $query->where('warehouse_id', $warehouseFilter);
            }

            if ($documentTypeFilter !== '') {
                $query->where('document_type', $documentTypeFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'warehouse_id', 'document_type', 'prefix', 'current_number', 'is_active', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $correlatives = $query->paginate($perPage);
            return ApiResponse::success($correlatives, 'Lista de correlativos', 200);
        } catch (Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'document_type' => 'required|string|max:50',
                'prefix' => 'required|string|max:20',
                'start_number' => 'nullable|integer|min:1',
                'padding_length' => 'nullable|integer|min:1|max:10',
                'is_active' => 'boolean',
                'description' => 'nullable|string',
            ]);

            $correlative = $this->correlativeService->createCorrelative($validated);

            return ApiResponse::success($correlative, 'Correlativo creado exitosamente', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al crear el correlativo', 400);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $correlative = Correlative::with('warehouse')->findOrFail($id);

            // Agregar el formato actual
            $result = $correlative->toArray();
            $result['current_formatted'] = $correlative->getCurrentFormatted();
            $result['next_formatted'] = $correlative->generateNext();

            return ApiResponse::success($result, 'Detalle del correlativo', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Correlativo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el correlativo', 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'sometimes|exists:warehouses,id',
                'document_type' => 'sometimes|string|max:50',
                'prefix' => 'sometimes|string|max:20',
                'current_number' => 'sometimes|integer|min:0',
                'start_number' => 'sometimes|integer|min:1',
                'padding_length' => 'sometimes|integer|min:1|max:10',
                'is_active' => 'boolean',
                'description' => 'nullable|string',
            ]);

            $correlative = $this->correlativeService->updateCorrelative($id, $validated);

            return ApiResponse::success($correlative, 'Correlativo actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Correlativo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar el correlativo', 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $correlative = Correlative::findOrFail($id);
            $correlative->delete();

            return ApiResponse::success(null, 'Correlativo eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Correlativo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el correlativo', 400);
        }
    }

    /**
     * Obtener el siguiente número correlativo
     */
    public function getNext(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'document_type' => 'required|string',
            ]);

            $nextNumber = $this->correlativeService->getNextNumber(
                $validated['warehouse_id'],
                $validated['document_type']
            );

            return ApiResponse::success(['next_number' => $nextNumber], 'Siguiente número obtenido', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el siguiente número', 400);
        }
    }

    /**
     * Resetear un correlativo
     */
    public function reset($id): JsonResponse
    {
        try {
            $correlative = $this->correlativeService->resetCorrelative($id);
            return ApiResponse::success($correlative, 'Correlativo reseteado exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al resetear el correlativo', 400);
        }
    }

    /**
     * Activar/Desactivar un correlativo
     */
    public function toggle(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $correlative = $this->correlativeService->toggleCorrelative($id, $validated['is_active']);
            return ApiResponse::success($correlative, 'Estado del correlativo actualizado', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar el estado', 400);
        }
    }

    /**
     * Obtener correlativos por sucursal
     */
    public function getByWarehouse($warehouseId): JsonResponse
    {
        try {
            $correlatives = $this->correlativeService->getByWarehouse($warehouseId);
            return ApiResponse::success($correlatives, 'Correlativos obtenidos exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los correlativos', 500);
        }
    }

    /**
     * Obtener tipos de documentos disponibles
     */
    public function getDocumentTypes(): JsonResponse
    {
        try {
            $types = $this->correlativeService->getDocumentTypes();
            return ApiResponse::success($types, 'Tipos de documentos obtenidos', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los tipos', 500);
        }
    }

    /**
     * Estadísticas
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Correlative::count();
            $active = Correlative::where('is_active', 1)->count();
            $inactive = Correlative::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ];

            return ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }

    /**
     * Acciones grupales
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            // Activar cada uno usando el servicio para mantener la lógica de negocio
            foreach ($ids as $id) {
                $this->correlativeService->toggleCorrelative($id, true);
            }

            return ApiResponse::success(null, 'Correlativos activados exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al activar los correlativos', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Correlative::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Correlativos desactivados exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al desactivar los correlativos', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Correlative::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Correlativos eliminados exitosamente', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar los correlativos', 500);
        }
    }
}
