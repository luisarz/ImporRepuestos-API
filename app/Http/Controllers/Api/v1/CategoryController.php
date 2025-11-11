<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CategoryStoreRequest;
use App\Http\Requests\Api\v1\CategoryUpdateRequest;
use App\Http\Resources\Api\v1\CategoryCollection;
use App\Http\Resources\Api\v1\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
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

            $query = Category::query()->with('categoryParent')->withCount('products');

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

            $categories = $query->paginate($perPage);
            return ApiResponse::success($categories, 'Categorías recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = (new \App\Models\Category)->create($request->validated());
            return ApiResponse::success(new CategoryResource($category), 'Category created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrio un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $category = (new \App\Models\Category)->findOrFail($id);
            return ApiResponse::success(new CategoryResource($category), 'Categoría recuperada', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Categoría no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $category = (new \App\Models\Category)->findOrFail($id);
            $category->update($request->validated());
            return ApiResponse::success(new CategoryResource($category), 'Categoría actualizada', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Categoría no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $category = (new \App\Models\Category)->findOrFail($id);
            $category->delete();
            return ApiResponse::success(null, 'Categoría eliminada de manera exitosa', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(),'Categoría no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = Category::count();
            $active = Category::where('is_active', 1)->count();
            $inactive = Category::where('is_active', 0)->count();

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
            $categories = Category::whereIn('id', $ids)->withCount('products')->get();
            return ApiResponse::success($categories, 'Categorías recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Category::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Categorías activadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Category::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Categorías desactivadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Category::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Categorías eliminadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener datos para exportar con filtros opcionales
     */
    public function getExportData(Request $request): JsonResponse
    {
        try {
            $categoryParentId = $request->input('category_parent_id', null);
            $statusFilter = $request->input('status_filter', '');

            $query = Category::query()->with('categoryParent');

            // Filtrar por categoría padre si se proporciona
            if ($categoryParentId !== null && $categoryParentId !== '') {
                $query->where('category_parent_id', $categoryParentId);
            }

            // Filtrar por estado si se proporciona
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Ordenar por ID
            $query->orderBy('id', 'asc');

            $categories = $query->get();
            return ApiResponse::success($categories, 'Datos para exportar obtenidos exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
