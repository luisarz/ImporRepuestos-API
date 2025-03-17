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

            $brands = Brand::paginate($perPage);
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
}
