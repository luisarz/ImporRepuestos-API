<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProductStoreRequest;
use App\Http\Requests\Api\v1\ProductUpdateRequest;
use App\Http\Resources\Api\v1\ProductCollection;
use App\Http\Resources\Api\v1\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
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

            // Validar per_page
            if (!is_numeric($perPage) || $perPage < 1) {
                $perPage = 10;
            }
            $perPage = min((int)$perPage, 100); // Máximo 100 registros por página

            // Validar sortField: si es "null" string o vacío, usar 'id'
            if (!$sortField || $sortField === 'null' || $sortField === 'undefined') {
                $sortField = 'id';
            }

            // Mapear campos de relaciones a campos reales de la tabla
            $sortFieldMap = [
                'category' => 'category_id',
                'brand' => 'brand_id',
                'unitMeasurement' => 'unit_measurement_id',
            ];

            // Si el campo está en el mapeo, usar el campo mapeado
            if (isset($sortFieldMap[$sortField])) {
                $sortField = $sortFieldMap[$sortField];
            }

            // Validar sortOrder
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query = Product::with(
                'brand:id,code,description',
                'category:id,code,description',
                'provider:id,comercial_name,document_number',
                'unitMeasurement:id,code,description',
                'applications',
                'images' // Cargar imágenes del producto
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

            // Aplicar ordenamiento (siempre ordenar por algo)
            $query->orderBy($sortField, $sortOrder);

            $products = $query->paginate($perPage);
            return ApiResponse::success($products, 'Productos recuperados exitosamente' , 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            \Log::info('=== CREANDO PRODUCTO ===');
            \Log::info('Datos validados:', $request->validated());

            // Crear el producto con los datos validados
            $data = $request->validated();

            // IMPORTANTE: Remover la imagen de los datos antes de crear
            // La imagen se procesará después de crear el registro
            unset($data['image']);

            // IMPORTANTE: Marcar como NO temporal (producto real)
            $data['is_temp'] = false;

            // Crear el producto
            $product = Product::create($data);
            \Log::info('Producto creado con ID: ' . $product->id);

            // Manejar múltiples imágenes si existen
            if ($request->hasFile('images')) {
                \Log::info('Procesando imágenes...');

                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }

                foreach ($images as $index => $image) {
                    try {
                        // Validar imagen
                        if (!$image->isValid()) {
                            \Log::warning("Imagen {$index} no válida, saltando...");
                            continue;
                        }

                        // Generar nombre único
                        $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();

                        // Guardar imagen en storage/app/public/products
                        $path = $image->storeAs('products', $imageName, 'public');

                        if (!$path) {
                            \Log::error("Error al guardar imagen {$index}");
                            continue;
                        }

                        // Crear registro de imagen
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'is_primary' => $index === 0, // Primera imagen es la principal
                            'order' => $index,
                        ]);

                        \Log::info("Imagen {$index} guardada: {$path}");

                    } catch (\Exception $e) {
                        \Log::error("Error al procesar imagen {$index}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            // Recargar el producto con sus relaciones e imágenes
            $product->load('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description', 'images');

            \Log::info('Producto creado exitosamente:', ['id' => $product->id, 'is_temp' => $product->is_temp]);

            return ApiResponse::success($product, 'Producto creado exitosamente', 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear producto: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::with('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description', 'images')->findOrFail($id);
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
            \Log::info('=== ACTUALIZANDO PRODUCTO ===');
            \Log::info('ID: ' . $id);
            \Log::info('Datos recibidos:', $request->validated());

            $product = Product::findOrFail($id);

            // Procesar múltiples imágenes si existen
            if ($request->hasFile('images')) {
                \Log::info('Subiendo imágenes...');

                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }

                // Obtener el número actual de imágenes para continuar el orden
                $currentImagesCount = $product->images()->count();

                foreach ($images as $index => $image) {
                    try {
                        // Validar imagen
                        if (!$image->isValid()) {
                            \Log::warning("Imagen {$index} no válida, saltando...");
                            continue;
                        }

                        // Generar nombre único
                        $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();

                        // Guardar en storage/app/public/products
                        $path = $image->storeAs('products', $imageName, 'public');

                        if (!$path) {
                            \Log::error("Error al guardar imagen {$index}");
                            continue;
                        }

                        // Crear registro de imagen
                        // Si no hay imágenes, la primera será principal
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'is_primary' => $currentImagesCount === 0 && $index === 0,
                            'order' => $currentImagesCount + $index,
                        ]);

                        \Log::info("Imagen {$index} guardada: {$path}");

                    } catch (\Exception $e) {
                        \Log::error("Error al procesar imagen {$index}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            // Actualizar con los datos validados
            $data = $request->validated();

            // IMPORTANTE: Remover las imágenes de los datos
            // Las imágenes ya fueron procesadas arriba
            unset($data['images']);
            unset($data['image']);

            // IMPORTANTE: Al actualizar, siempre marcar como NO temporal
            $data['is_temp'] = false;

            $product->update($data);

            // Recargar con relaciones e imágenes
            $product->load('brand:id,code,description', 'category:id,code,description', 'provider:id,comercial_name,document_number', 'unitMeasurement:id,code,description', 'images');

            \Log::info('Producto actualizado exitosamente:', ['id' => $product->id, 'is_temp' => $product->is_temp]);

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

    /**
     * Eliminar una imagen específica del producto
     */
    public function deleteImage($productId, $imageId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);
            $image = ProductImage::where('product_id', $productId)
                                 ->where('id', $imageId)
                                 ->firstOrFail();

            $wasPrimary = $image->is_primary;
            $image->delete();

            // Si la imagen eliminada era la principal, asignar otra como principal
            if ($wasPrimary) {
                $newPrimary = $product->images()->orderBy('order')->first();
                if ($newPrimary) {
                    $newPrimary->is_primary = true;
                    $newPrimary->save();
                }
            }

            return ApiResponse::success(null, 'Imagen eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Imagen no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Establecer una imagen como principal
     */
    public function setPrimaryImage($productId, $imageId): JsonResponse
    {
        try {
            $product = Product::findOrFail($productId);

            // Quitar el flag de principal de todas las imágenes del producto
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);

            // Establecer la nueva imagen principal
            $image = ProductImage::where('product_id', $productId)
                                 ->where('id', $imageId)
                                 ->firstOrFail();

            $image->is_primary = true;
            $image->save();

            return ApiResponse::success($image, 'Imagen principal actualizada', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Imagen no encontrada', 404);
        } catch (\Exception $e) {
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
