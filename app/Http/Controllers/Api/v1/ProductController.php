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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $sortField = $request->input('sortField', 'id');
            $sortOrder = $request->input('sortOrder', 'desc');
            $search = $request->input('search', null);
            $isActive = $request->input('is_active', null);

            $query = Product::with(
                'brand:id,code,description',
                'category:id,code,description',
                'provider:id,comercial_name,document_number',
                'unitMeasurement:id,code,description',
                'applications'
            )->where('is_temp', 0);

            // Aplicar búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('original_code', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            }

            // Aplicar filtro de estado
            if ($isActive !== null && $isActive !== '') {
                $query->where('is_active', $isActive);
            }

            // Aplicar ordenamiento
            if ($sortField) {
                $query->orderBy($sortField, $sortOrder);
            }

            $products = $query->paginate($perPage);
            return ApiResponse::success($products, 'Productos recuperados exitosamente' , 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {



            $product = (new \App\Models\Product)->create($request->validated());
            // Si se sube una imagen, guárdala

            return ApiResponse::success($product, 'Producto creado exitosamente' . $request, 201);
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

            $product = Product::findOrFail($id);


            if ($request->hasFile('image')) {
                \Log::info('Subiendo imagen...');

                try {
                    $image = $request->file('image');

                    // 1. Validaciones previas
                    if (!$image->isValid()) {
                        throw new \Exception('Archivo de imagen no válido');
                    }

                    // 2. Generar nombre único
                    $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();

                    // 3. Guardar con verificación
                    //guredala en public_path
                    $path = $image->storeAs('products', $imageName, 'public');

                    if (!$path) {
                        throw new \Exception('Error al mover el archivo al almacenamiento');
                    }



                    // 5. Eliminar imagen anterior si existe
                    if ($product->image) {
                        Storage::disk('public')->delete($product->image);
                    }

                    // 6. Actualizar modelo
                    $product->image = Storage::url($path);
                    $product->save();

                    \Log::info('Imagen asignada al producto: ' . $product->image);

                } catch (\Exception $e) {
                    \Log::error('Error al procesar imagen: ' . $e->getMessage());
                    // Opcional: Retornar error al cliente
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al subir imagen: ' . $e->getMessage()
                    ], 500);
                }
            }
            $product->update($request->validated());

            // Log después de actualizar

            return ApiResponse::success($product, 'Producto actualizado exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            \Log::error('Producto no encontrado:', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error(null, 'Producto no encontrado', 404);
        } catch (\Exception $e) {
            \Log::error('Error actualizando producto:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

    /**
     * Activar productos por lotes
     */
    public function batchActivate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id'
            ]);

            $updated = Product::whereIn('id', $request->ids)->update(['is_active' => 1]);

            return ApiResponse::success([
                'updated_count' => $updated,
                'ids' => $request->ids
            ], "{$updated} productos activados exitosamente", 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al activar productos', 500);
        }
    }

    /**
     * Desactivar productos por lotes
     */
    public function batchDeactivate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id'
            ]);

            $updated = Product::whereIn('id', $request->ids)->update(['is_active' => 0]);

            return ApiResponse::success([
                'updated_count' => $updated,
                'ids' => $request->ids
            ], "{$updated} productos desactivados exitosamente", 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al desactivar productos', 500);
        }
    }

    /**
     * Eliminar productos por lotes
     */
    public function batchDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id'
            ]);

            $deleted = Product::whereIn('id', $request->ids)->delete();

            return ApiResponse::success([
                'deleted_count' => $deleted,
                'ids' => $request->ids
            ], "{$deleted} productos eliminados exitosamente", 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar productos', 500);
        }
    }

    /**
     * Obtener estadísticas de productos
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $total = Product::where('is_temp', 0)->count();
            $active = Product::where('is_temp', 0)->where('is_active', 1)->count();
            $discontinued = Product::where('is_temp', 0)->where('is_discontinued', 1)->count();

            return ApiResponse::success([
                'total' => $total,
                'active' => $active,
                'discontinued' => $discontinued
            ], 'Estadísticas recuperadas exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }
}
