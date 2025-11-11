<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProvidersTypeStoreRequest;
use App\Http\Requests\Api\v1\ProvidersTypeUpdateRequest;
use App\Http\Resources\Api\v1\ProvidersTypeCollection;
use App\Http\Resources\Api\v1\ProvidersTypeResource;
use App\Models\ProvidersType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProvidersTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
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

            $query = ProvidersType::query();

            // Búsqueda por múltiples campos
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

            // Aplicar ordenamiento
            $allowedSortFields = ['id', 'code', 'description', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $providersTypes = $query->paginate($perPage);
            return ApiResponse::success($providersTypes, 'Tipos de proveedores obtenidos exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(ProvidersTypeStoreRequest $request): JsonResponse
    {
        try {
            $providersType = ProvidersType::create($request->validated());
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor creado exitosamente',201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor obtenido exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(ProvidersTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            $providersType->update($request->validated());
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor actualizado exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            $providersType->delete();
           return ApiResponse::success(null, 'Tipo de proveedor eliminado exitosamente',204);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function stats(): JsonResponse
    {
        try {
            $total = ProvidersType::count();
            $active = ProvidersType::where('is_active', 1)->count();
            $inactive = ProvidersType::where('is_active', 0)->count();

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
            $items = ProvidersType::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Tipos de proveedor recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProvidersType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de proveedor activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProvidersType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de proveedor desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProvidersType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de proveedor eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
