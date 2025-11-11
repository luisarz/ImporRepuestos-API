<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ContingencyTypeStoreRequest;
use App\Http\Requests\Api\v1\ContingencyTypeUpdateRequest;
use App\Models\ContingencyType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContingencyTypeController extends Controller
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

            $query = ContingencyType::query();

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro de estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'code', 'description', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $contingencyTypes = $query->paginate($perPage);
            return ApiResponse::success($contingencyTypes, 'Tipos de contingencia recuperados exitosamente', 200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(ContingencyTypeStoreRequest $request): JsonResponse
    {
        try {
            $contingencyType = ContingencyType::create($request->validated());
            return ApiResponse::success($contingencyType, 'Tipo de contingencia creado exitosamente', 201);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $contingencyType = ContingencyType::findOrFail($id);
            return ApiResponse::success($contingencyType, 'Tipo de contingencia recuperado', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de contingencia no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(ContingencyTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $contingencyType = ContingencyType::findOrFail($id);
            $contingencyType->update($request->validated());
            return ApiResponse::success($contingencyType, 'Tipo de contingencia actualizado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de contingencia no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $contingencyType = ContingencyType::findOrFail($id);
            $contingencyType->delete();
            return ApiResponse::success(null, 'Tipo de contingencia eliminado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de contingencia no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = ContingencyType::count();
            $active = ContingencyType::where('is_active', 1)->count();
            $inactive = ContingencyType::where('is_active', 0)->count();

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
            $items = ContingencyType::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Tipos de contingencia recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ContingencyType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de contingencia activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ContingencyType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de contingencia desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ContingencyType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de contingencia eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
