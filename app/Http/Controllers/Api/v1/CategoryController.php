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
        $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto

        try {
            $categories = Category::with('categoryParent')->paginate($perPage);
            return ApiResponse::success($categories, 'Categories retrieved successfully', 200);
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
            $category = (new \App\Models\Category)->findOrFail($id); // No es necesario usar "new"
            $category->delete();
            return ApiResponse::success(null, 'Categoría eliminada', 200); // Retornar la respuesta correcta
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 'Categoría no encontrada', 404); // Retornar el error explícitamente
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500); // Retornar el error explícitamente
        }
    }

}
