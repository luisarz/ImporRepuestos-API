<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\MunicipalityStoreRequest;
use App\Http\Requests\Api\v1\MunicipalityUpdateRequest;
use App\Http\Resources\Api\v1\MunicipalityCollection;
use App\Http\Resources\Api\v1\MunicipalityResource;
use App\Models\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class MunicipalityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = Municipality::query()->with('department');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            $allowedSortFields = ['id', 'code', 'description', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $municipalities = $query->paginate($perPage);
            return ApiResponse::success($municipalities, 'Municipios recuperados con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(MunicipalityStoreRequest $request): JsonResponse
    {
        try {
            $municipality = (new \App\Models\Municipality)->create($request->validated());
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio creado con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al crear el municipio', 500);
        }
    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $municipality = Municipality::with('department')->find($id);
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio recuperado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al recuperar el municipio', 500);
        }
    }

    public function update(MunicipalityUpdateRequest $request, $id): JsonResponse
    {
        try {
            $municipality = (new Municipality)->findOrFail($id);
            if (!$municipality) {
                return ApiResponse::error('','Municipio no encontrado', 404);
            }
            $municipality->update($request->validated());
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio actualizado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al actualizar el municipio', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $municipality = (new Municipality)->findOrFail($id);
            if (!$municipality) {
                return ApiResponse::error('','Municipio no encontrado', 404);
            }
            $municipality->delete();
            return ApiResponse::success('','Municipio eliminado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al eliminar el municipio', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = Municipality::count();
            $active = Municipality::where('is_active', 1)->count();
            $inactive = Municipality::where('is_active', 0)->count();

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
            $municipalities = Municipality::whereIn('id', $ids)->with('department')->get();
            return ApiResponse::success($municipalities, 'Municipios recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Municipality::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Municipios activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Municipality::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Municipios desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Municipality::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Municipios eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
