<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProductStoreRequest;
use App\Http\Requests\Api\v1\ProductUpdateRequest;
use App\Http\Resources\Api\v1\ProductCollection;
use App\Http\Resources\Api\v1\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $products = Product::with('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description','applications')->paginate(10);
            return ApiResponse::success($products, 'Productos recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            $product = (new \App\Models\Product)->create($request->validated());
            return ApiResponse::success($product, 'Producto creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::with('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description')->findOrFail($id);
            return ApiResponse::success($product, 'Producto recuperado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Producto no encontrado', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(ProductUpdateRequest $request, $id): JsonResponse
    {
        try {
            $product = (new \App\Models\Product)->findOrFail($id);
            $product->update($request->validated());
            return ApiResponse::success(new ProductResource($product), 'Producto actualizado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Producto no encontrado', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $product = (new \App\Models\Product)->findOrFail($id);
            $product->delete();
            return ApiResponse::success(null, 'Producto eliminado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
           return ApiResponse::error(null, 'Producto no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
}
