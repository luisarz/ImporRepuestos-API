<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StablishmentTypeStoreRequest;
use App\Http\Requests\Api\v1\StablishmentTypeUpdateRequest;
use App\Http\Resources\Api\v1\StablishmentTypeCollection;
use App\Http\Resources\Api\v1\StablishmentTypeResource;
use App\Models\StablishmentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StablishmentTypeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
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

            $query = StablishmentType::query();

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

            $establishmentTypes = $query->paginate($perPage);
            return ApiResponse::success($establishmentTypes, 'Tipos de establecimientos recuperados exitosamente', 200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(StablishmentTypeStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType = StablishmentType::create($request->validated());
            return ApiResponse::success(new StablishmentTypeResource($establishmentType), 'Tipo de establecimiento creado de manera exitosa!', 201);

        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no creado', 400);
        }

    }

    public function show(Request $request, StablishmentType $establishmentType): \Illuminate\Http\JsonResponse
    {
        try {
          return ApiResponse::success($establishmentType, 'Detalle del tipo de establecimiento', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no encontrado', 404);
        }
    }

    public function update(StablishmentTypeUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType=StablishmentType::find($id);
            if(!$establishmentType){
                return ApiResponse::error(null, 'Tipo de establecimiento no encontrado', 404);
            }
            $establishmentType->update($request->validated());
            return ApiResponse::success(new StablishmentTypeResource($establishmentType), 'Tipo de establecimiento actualizado de manera exitosa', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no actualizado', 400);
        }

    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType=StablishmentType::find($id);
            if(!$establishmentType){
                return ApiResponse::error(null, 'Tipo de establecimiento no encontrado', 404);
            }
            $establishmentType->delete();
            return ApiResponse::success(null, 'Tipo de establecimiento eliminado de manera exitosa', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no eliminado', 400);
        }

    }

    public function stats(): \Illuminate\Http\JsonResponse
    {
        try {
            $total = StablishmentType::count();
            $active = StablishmentType::where('is_active', 1)->count();
            $inactive = StablishmentType::where('is_active', 0)->count();

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
    public function bulkGet(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $items = StablishmentType::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Tipos de establecimiento recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            StablishmentType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de establecimiento activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            StablishmentType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de establecimiento desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            StablishmentType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de establecimiento eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
