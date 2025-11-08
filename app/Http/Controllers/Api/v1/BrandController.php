<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BrandStoreRequest;
use App\Http\Requests\Api\v1\BrandUpdateRequest;
use App\Http\Resources\Api\v1\BrandCollection;
use App\Http\Resources\Api\v1\BrandResource;
use App\Models\Brand;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
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

            $query = Brand::query()->withCount('products');

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

            $brands = $query->paginate($perPage);
            return ApiResponse::success($brands, 'Marcas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(BrandStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $brand = (new \App\Models\Brand)->create($request->validated());
            return ApiResponse::success($brand, 'Marca creada de manera exitosa', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $brand = (new \App\Models\Brand)->findOrFail($id);
           return ApiResponse::success(new BrandResource($brand), 'Marca recuperada de manera exitosa', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),'Marca no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(BrandUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $brand = (new \App\Models\Brand)->findOrFail($id);
            $brand->update($request->validated());
            return ApiResponse::success(new BrandResource($brand), 'Marca actualizada de manera exitosa', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),'Marca no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $brand = (new \App\Models\Brand)->findOrFail($id);
            $brand->delete();
            return ApiResponse::success(null, 'Marca eliminada de manera exitosa', 200);
        } catch (ModelNotFoundException $e) {
        return ApiResponse::error($e->getMessage(),'Marca no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function stats(): \Illuminate\Http\JsonResponse
    {
        try {
            $total = Brand::count();
            $active = Brand::where('is_active', 1)->count();
            $inactive = Brand::where('is_active', 0)->count();

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
            $brands = Brand::whereIn('id', $ids)->withCount('products')->get();
            return ApiResponse::success($brands, 'Marcas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Brand::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Marcas activadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Brand::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Marcas desactivadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Brand::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Marcas eliminadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
