<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DistrictStoreRequest;
use App\Http\Requests\Api\v1\DistrictUpdateRequest;
use App\Http\Resources\Api\v1\DistrictCollection;
use App\Http\Resources\Api\v1\DistrictResource;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DistrictController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $municipalityFilter = $request->input('municipality_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = District::with('municipality.department.country');

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('municipality', function($q) use ($search) {
                          $q->where('description', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro de estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Filtro de municipalidad
            if ($municipalityFilter !== '') {
                $query->where('municipality_id', $municipalityFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'code', 'description', 'municipality_id', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $districts = $query->paginate($perPage);
            return ApiResponse::success($districts, 'Distritos recuperados exitosamente', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(DistrictStoreRequest $request): JsonResponse
    {
        try {
            $district = (new District)->create($request->validated());
            return ApiResponse::success(new DistrictResource($district), 'District created successfully.', 201);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);

            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            return ApiResponse::success(new DistrictResource($district), 'Distrito encontrado', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function update(DistrictUpdateRequest $request, $id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);
            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            $district->update($request->validated());
            return ApiResponse::success(new DistrictResource($district), 'Distrito actualizado correctamente.', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);
            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            $district->delete();
            return ApiResponse::success(null, 'Distrito eliminado correctamente.', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    // Estadísticas
    public function stats(): JsonResponse
    {
        try {
            $total = District::count();
            $active = District::where('is_active', 1)->count();
            $inactive = District::where('is_active', 0)->count();

            return ApiResponse::success([
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ], 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $items = District::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Elementos recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            District::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Elementos activados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            District::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Elementos desactivados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            District::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Elementos eliminados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
