<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UnitMeasurementStoreRequest;
use App\Http\Requests\Api\v1\UnitMeasurementUpdateRequest;
use App\Http\Resources\Api\v1\UnitMeasurementCollection;
use App\Http\Resources\Api\v1\UnitMeasurementResource;
use App\Models\UnitMeasurement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnitMeasurementController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        try {
            // Soportar múltiples formatos de paginación
            $perPage = $request->input('length', $request->input('per_page', 10));
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');

            // El DataTable envía 'sortField' y 'sortOrder'
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            // Convertir a minúsculas y validar
            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc'; // Valor por defecto si no es válido
            }

            // Si per_page es muy grande o inválido, usar valor por defecto
            if (!is_numeric($perPage) || $perPage > 100 || $perPage < 1) {
                $perPage = 10;
            }

            $query = UnitMeasurement::query();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Aplicar ordenamiento - solo campos propios del modelo
            $allowedSortFields = ['id', 'code', 'description', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Obtener los datos paginados
            $unitMeasurements = $query->paginate($perPage);

            return ApiResponse::success($unitMeasurements, 'Unidades de medida obtenidas correctamente',200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('No se encontraron unidades de medida', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(UnitMeasurementStoreRequest $request): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->create($request->validated());
            return ApiResponse::success($unitMeasurement, 'Unidad de medida creada correctamente', 200);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            return ApiResponse::success($unitMeasurement, 'Unidad de medida obtenida correctamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al obtener la unidad de medida', 500);
        }
    }

    public function update(UnitMeasurementUpdateRequest $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            $unitMeasurement->update($request->validated());
         return ApiResponse::success($unitMeasurement, 'Unidad de medida actualizada correctamente', 200);
        } catch (ModelNotFoundException $e) {
           return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al actualizar la unidad de medida', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            $unitMeasurement->delete();
            return ApiResponse::success(null, 'Unidad de medida eliminada correctamente', 204);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al eliminar la unidad de medida', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = UnitMeasurement::count();
            $active = UnitMeasurement::where('is_active', 1)->count();
            $inactive = UnitMeasurement::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $unitMeasurements = UnitMeasurement::whereIn('id', $ids)->get();
            return ApiResponse::success($unitMeasurements, 'Unidades de medida recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            UnitMeasurement::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Unidades de medida activadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            UnitMeasurement::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Unidades de medida desactivadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            UnitMeasurement::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Unidades de medida eliminadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
