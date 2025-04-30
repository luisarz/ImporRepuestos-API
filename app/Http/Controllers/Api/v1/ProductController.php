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
            $sortField = $request->input('sortField', null);
            $sortOrder = $request->input('sortOrder', 'asc');

            $products = Product::with('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description','applications')
//                ->orderBy($sortField, $sortOrder)
                ->paginate($perPage);
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
}
