<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoryStoreRequest;
use App\Http\Requests\Api\v1\InventoryUpdateRequest;
use App\Http\Resources\Api\v1\InventoryCollection;
use App\Http\Resources\Api\v1\InventoryResource;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Price;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $filtersJson = $request->input('filters') ?? '[]';
            $filters = json_decode($filtersJson, true) ?? [];

            $inventories = Inventory::with([
                'warehouse:id,name',
                'product:id,code,original_code,description,category_id,unit_measurement_id,image,barcode,description_measurement_id,brand_id',
                'prices',
                'product.category',
                'product.brand',
                'product.unitMeasurement',
                'product.images',
            ])
                ->withSum('inventoryBatches', 'quantity')
                ->whereHas('product', function ($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('original_code', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });

            // Filtrar solo productos con existencias
            $withStock = $request->input('with_stock', false);
            if ($withStock === 'true' || $withStock === true || $withStock === '1') {
                $inventories->having('inventory_batches_sum_quantity', '>', 0);
            }

            // 🔍 Aplicar filtros dinámicamente
            foreach ($filters as $filter) {
                $column = $filter['column'] ?? null;
                $value = $filter['value'] ?? null;
                $type = $filter['type'] ?? 'text';

                if (!$column || $value === null) continue;

                // Verifica si es un filtro sobre relación (usa notación punto)
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $inventories->whereHas($relation, function ($q) use ($field, $value) {
                        $q->where($field, 'like', "%$value%");
                    });
                } else {
                    $inventories->where($column, 'like', "%$value%");
                }
            }

            $inventories = $inventories->paginate($perPage);

            $inventories->getCollection()->transform(function ($inventory) {
                $stock = $inventory->inventoryBatches->sum('quantity');
                $inventory->actual_stock = number_format($stock ?? 0, 2);

                $price = $inventory->prices->firstWhere('is_default', 1)?->price ?? 0;
                $inventory->default_price = number_format($price, 2);

                return $inventory;
            });

            log::info('Inventories retrieved successfully', [
                'count' => $inventories->count(),
                'perPage' => $perPage,
                'search' => $search,
                'filters' => $filters
            ]);

            return ApiResponse::success($inventories, 'Inventarios recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function getPrices($idInventory,Request $request): JsonResponse
    {
        \Illuminate\Log\log($idInventory);
        try {
            $perPage = $request->input('per_page', 10);
            $prices = Price::where('inventory_id', $idInventory)->paginate($perPage);
            return ApiResponse::success($prices, 'Precios recuperados exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'No se encontró el id inventario', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    /**
     * Obtiene todos los inventarios de un producto específico en todas las sucursales
     *
     * @param int $productId ID del producto
     * @return JsonResponse
     */
    public function getByProduct($productId): JsonResponse
    {
        try {
            $inventories = Inventory::with([
                'warehouse:id,name,address,phone',
                'product:id,code,original_code,description',
                'prices' => function($query) {
                    $query->where('is_default', true)->where('is_active', true);
                }
            ])
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->where('is_temp', false)
            ->get();

            // Calcular stock actual de cada inventario sumando los batches
            $inventories->each(function ($inventory) {
                $stock = $inventory->inventoryBatches->sum('quantity');
                $inventory->actual_stock = $stock ?? 0;

                // Obtener precio predeterminado
                $defaultPrice = $inventory->prices->first();
                $inventory->default_price = $defaultPrice ? $defaultPrice->price : 0;

                // Limpiar relación de precios para no enviar data innecesaria
                unset($inventory->prices);
            });

            return ApiResponse::success($inventories, 'Inventarios recuperados exitosamente', 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener inventarios por producto', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al obtener los inventarios', 500);
        }
    }

    public function store(InventoryStoreRequest $request): JsonResponse
    {
        try {
            $inventory = (new Inventory)->create($request->validated());
            return ApiResponse::success($inventory, 'Inventario creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $inventory = Inventory::with('warehouse:id,name', 'product:id,code,original_code,description', 'prices')->findOrFail($id);
            return ApiResponse::success($inventory, 'Inventario recuperado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtiene información detallada del inventario incluyendo:
     * - Datos del inventario actual
     * - Información completa del producto (imágenes, aplicaciones, intercambios, equivalentes)
     * - Existencias en otras sucursales
     *
     * @param int $id ID del inventario
     * @return JsonResponse
     */
    public function getDetails($id): JsonResponse
    {
        try {
            // 1. Obtener el inventario actual con sus relaciones básicas
            $inventory = Inventory::with([
                'warehouse:id,name,address,phone',
                'product:id,code,original_code,description,category_id,unit_measurement_id,image,barcode,description_measurement_id,brand_id',
                'prices',
                'product.category:id,description',
                'product.brand:id,description',
                'product.unitMeasurement:id,description',
                'product.images',
            ])
            ->withSum('inventoryBatches', 'quantity')
            ->findOrFail($id);

            // Calcular stock actual
            $inventory->actual_stock = $inventory->inventory_batches_sum_quantity ?? 0;

            // 2. Obtener información completa del producto
            $productId = $inventory->product_id;

            // Aplicaciones (vehículos compatibles)
            try {
                $applications = \App\Models\Application::where('product_id', $productId)
                    ->where('is_active', 1)
                    ->with(['vehicle'])
                    ->get()
                    ->map(function ($app) {
                        $vehicle = $app->vehicle;

                        // Si no hay vehículo asociado, solo usar los datos del application
                        if (!$vehicle) {
                            return [
                                'id' => $app->id,
                                'application_name' => $app->name ?? '',
                                'application_brand' => $app->brand ?? '',
                                'year' => null,
                                'motor' => null,
                                'motor_type' => null,
                                'vehicle_model_description' => null,
                                'vehicle_brand_description' => null,
                            ];
                        }

                        // Cargar modelo y marca manualmente si existen
                        $model = null;
                        $brand = null;

                        if ($vehicle->model_id) {
                            $model = \App\Models\VehicleModel::find($vehicle->model_id);
                            if ($model && $model->brand_id) {
                                $brand = \App\Models\Brand::find($model->brand_id);
                            }
                        }

                        return [
                            'id' => $app->id,
                            'application_name' => $app->name ?? '',
                            'application_brand' => $app->brand ?? '',
                            'year' => $vehicle->year ?? null,
                            'motor' => $vehicle->motor ?? null,
                            'motor_type' => $vehicle->motor_type ?? null,
                            'vehicle_model_description' => $model->description ?? null,
                            'vehicle_brand_description' => $brand->description ?? null,
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error al obtener aplicaciones', [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $applications = collect([]);
            }

            // Intercambios
            try {
                $interchanges = \DB::table('interchanges')
                    ->leftJoin('brands', 'interchanges.brand_id', '=', 'brands.id')
                    ->where('interchanges.product_id', $productId)
                    ->select(
                        'interchanges.id',
                        'interchanges.interchange_code',
                        'brands.description as brand_description'
                    )
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error al obtener intercambios', [
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ]);
                $interchanges = collect([]);
            }

            // Equivalentes
            try {
                $equivalents = \DB::table('equivalents')
                    ->join('products as equivalent_products', 'equivalents.equivalent_product_id', '=', 'equivalent_products.id')
                    ->where('equivalents.product_id', $productId)
                    ->select(
                        'equivalents.id',
                        'equivalent_products.code',
                        'equivalent_products.description'
                    )
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error al obtener equivalentes', [
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ]);
                $equivalents = collect([]);
            }

            // 3. Obtener existencias en otras sucursales
            try {
                $otherInventories = Inventory::with('warehouse:id,name,address')
                    ->where('product_id', $productId)
                    ->where('is_active', true)
                    ->where('is_temp', false)
                    ->withSum('inventoryBatches', 'quantity')
                    ->get()
                    ->map(function ($inv) use ($id) {
                        return [
                            'id' => $inv->id,
                            'warehouse_id' => $inv->warehouse_id,
                            'warehouse' => $inv->warehouse,
                            'stock' => $inv->inventory_batches_sum_quantity ?? 0,
                            'is_current' => $inv->id == $id,
                            'stock_min' => $inv->stock_min,
                            'stock_max' => $inv->stock_max,
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error al obtener inventarios de otras sucursales', [
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ]);
                $otherInventories = collect([]);
            }

            // 4. Construir respuesta
            $response = [
                'inventory' => $inventory,
                'product_details' => [
                    'applications' => $applications,
                    'interchanges' => $interchanges,
                    'equivalents' => $equivalents,
                ],
                'other_warehouses' => $otherInventories,
                'summary' => [
                    'total_stock_all_warehouses' => $otherInventories->sum('stock'),
                    'warehouses_count' => $otherInventories->count(),
                    'applications_count' => $applications->count(),
                    'interchanges_count' => $interchanges->count(),
                    'equivalents_count' => $equivalents->count(),
                ]
            ];

            Log::info('Detalles de inventario recuperados', [
                'inventory_id' => $id,
                'product_id' => $productId,
            ]);

            return ApiResponse::success($response, 'Detalles del inventario recuperados exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del inventario', [
                'inventory_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al obtener los detalles', 500);
        }
    }

    public function update(InventoryUpdateRequest $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $message = "";
            $inventory = Inventory::findOrFail($id);
            $existing = Inventory::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                $message.= 'Este producto ya existe en la sucursal que intentas levantarlo';
                return ApiResponse::error(null, $message, 200);
            }
            //validamos que sea temporal antes de actualizar
            $is_temp=false;
            if ($inventory->is_temp) {
                $is_temp=true;
            }

            $updated = $inventory->update($request->only([
                'warehouse_id',
                'product_id',
                'provider_id',
                'stock_actual_quantity',
                'stock_min',
                'stock_max',
                'last_cost_without_tax',
                'last_cost_with_tax',
                'alert_stock_min',
                'alert_stock_max',
                'is_active',
            ]));


            if ($updated) {
                //Crear un loto inicial separaado por el id del inventario
                if($is_temp){
                    $lote=new Batch();
                    $lote->code=$inventory->product->code;
                    $lote->origen_code=1;
                    $lote->inventory_id=$inventory->id;
                    $lote->incoming_date=now();
                    $lote->expiration_date=null;
                    $lote->initial_quantity=$request->stock_actual_quantity;
                    $lote->available_quantity=$request->stock_actual_quantity;
                    $lote->observations="Lote de Inventario inicial";
                    $lote->is_active=1;
                    if($lote->save()) {
                        $message .= 'Lote creado exitosamente. ';
                    } else {
                        $message .= 'Error al crear el lote. ';
                    }

                    //Crear un lote de inventario
                    $inventory->inventoryBatches()->create([
                        'inventory_id' => $inventory->id,
                        'id_batch' => $lote->id,
                        'quantity' => $request->stock_actual_quantity,
                        'operation_date' => now(),
                    ]);
                    $message .= 'Lote de inventario creado exitosamente. ';
                }





                DB::commit();
                return ApiResponse::success([
                    'inventory' => $inventory,
                    'updated' => true,
                ], $message, 200);
            } else {
                return ApiResponse::success([
                    'inventory' => $inventory,
                    'updated' => false,
                ], 'No se realizaron cambios en el inventario', 200);
            }
        }catch (ValidationException $e) {
            return ApiResponse::error(null, 'Error de validación', 200);
        }
        catch (ModelNotFoundException $e) {
            return ApiResponse::error('Inventario no encontrado', 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }




    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $inventory = (new Inventory)->findOrFail($id);
            $inventory->delete();
            return ApiResponse::success(null, 'Inventario eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Inventario no encontrado', 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
