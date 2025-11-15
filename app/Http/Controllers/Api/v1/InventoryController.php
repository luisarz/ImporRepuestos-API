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
                ->withCount('inventoryBatches as batches_count')
                ->whereHas('product', function ($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('original_code', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });

            // Filtro por bodega
            if ($request->has('warehouse_id') && $request->input('warehouse_id') !== '') {
                $inventories->where('warehouse_id', $request->input('warehouse_id'));
            }

            // Filtrar solo productos con existencias
            $withStock = $request->input('with_stock', false);
            if ($withStock === 'true' || $withStock === true || $withStock === '1') {
                $inventories->having('inventory_batches_sum_quantity', '>', 0);
            }

            // Filtro por estado de stock
            $stockFilter = $request->input('stock_filter', 'all');
            \Log::info('Stock filter recibido:', ['filter' => $stockFilter]);

            if ($stockFilter !== 'all' && $stockFilter !== '') {
                // Obtener todos primero para filtrar
                $allInventories = $inventories->get();
                \Log::info('Total inventarios antes de filtrar:', ['count' => $allInventories->count()]);

                // Filtrar según el tipo de stock
                $filtered = $allInventories->filter(function ($inventory) use ($stockFilter) {
                    $actual = (float) ($inventory->inventory_batches_sum_quantity ?? 0);
                    $min = (float) ($inventory->stock_min ?? 0);

                    $result = false;
                    switch ($stockFilter) {
                        case 'low_stock':
                            // Stock bajo:
                            // - Si tiene mínimo configurado (min > 0): actual < min Y actual > 0
                            // - Si NO tiene mínimo configurado (min = 0): actual > 0 Y actual <= 10 (stock bajo sin mínimo)
                            if ($min > 0) {
                                $result = $actual < $min && $actual > 0;
                            } else {
                                $result = $actual > 0 && $actual <= 10; // Considera "bajo" si tiene 10 o menos sin mínimo configurado
                            }
                            break;
                        case 'no_stock':
                            // Sin stock: exactamente 0
                            $result = $actual == 0;
                            break;
                        case 'healthy_stock':
                            // Stock saludable:
                            // - Si tiene mínimo configurado: actual >= min Y actual > 0
                            // - Si NO tiene mínimo configurado: actual > 10
                            if ($min > 0) {
                                $result = $actual >= $min && $actual > 0;
                            } else {
                                $result = $actual > 10; // Saludable si tiene más de 10
                            }
                            break;
                        default:
                            $result = true;
                    }

                    // Log de algunos ejemplos
                    if ($inventory->id <= 5) {
                        \Log::info("Inventario {$inventory->id}: actual={$actual}, min={$min}, filter={$stockFilter}, result=" . ($result ? 'SI' : 'NO'));
                    }

                    return $result;
                });

                \Log::info('Total inventarios después de filtrar:', ['count' => $filtered->count()]);

                // Paginar manualmente
                $page = $request->input('page', 1);
                $total = $filtered->count();
                $items = $filtered->forPage($page, $perPage)->values();

                $inventories = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
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
            }

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
            // Crear el inventario (puede ser temporal is_temp=1 o permanente is_temp=0)
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

            // Verificar si existe otro inventario con el mismo producto en la misma sucursal
            $existing = Inventory::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                $message = 'Este producto ya existe en la sucursal que intentas levantarlo';
                return ApiResponse::error(null, $message, 200);
            }

            // Verificar si era temporal ANTES de actualizar
            $wasTemporary = $inventory->is_temp == 1;
            $stockQuantity = $request->stock_actual_quantity ?? 0;

            // Actualizar el inventario (incluyendo is_temp)
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
                'is_temp',  // ← Incluir is_temp en la actualización
            ]));

            if ($updated) {
                // Si era temporal y ahora es permanente (is_temp cambió de 1 a 0)
                // Y tiene stock inicial, crear el lote de inventario inicial
                if ($wasTemporary && $request->is_temp == 0 && $stockQuantity > 0) {

                    // Obtener o crear el código de origen para inventario inicial
                    $initialOriginCode = \App\Models\BatchCodeOrigen::firstOrCreate(
                        ['code' => 'INV_INICIAL'],
                        ['description' => 'INVENTARIO INICIAL', 'is_active' => 1]
                    );

                    // Crear el lote con código estandarizado
                    $batch = \App\Models\Batch::create([
                        'code' => 'INV-' . $inventory->id . '-INICIAL',
                        'purchase_item_id' => null,
                        'origen_code' => $initialOriginCode->id,
                        'inventory_id' => $inventory->id,
                        'incoming_date' => now(),
                        'expiration_date' => null,
                        'initial_quantity' => $stockQuantity,
                        'available_quantity' => $stockQuantity,
                        'observations' => 'Lote de Inventario inicial',
                        'is_active' => true,
                    ]);

                    // Crear el registro en inventories_batches
                    $inventoryBatch = \App\Models\InventoriesBatch::create([
                        'id_inventory' => $inventory->id,
                        'id_batch' => $batch->id,
                        'quantity' => $stockQuantity,
                        'operation_date' => now(),
                    ]);

                    // Registrar en Kardex el ingreso inicial
                    \App\Helpers\KardexHelper::createKardexFromInventory(
                        $inventory->warehouse_id,
                        now()->toDateTimeString(),
                        'INVENTARIO_INICIAL',
                        (string) $inventory->id,
                        (string) $batch->id,
                        'INGRESO',
                        'INV-INICIAL-' . $inventory->id,
                        'Sistema',
                        'N/A',
                        $inventory->id,
                        0,
                        (int) $stockQuantity,
                        0,
                        (int) $stockQuantity,
                        0.0,
                        0.0,
                        0.0,
                        0.0,
                        (float) ($inventory->last_cost_with_tax ?? 0),
                        $inventoryBatch->id
                    );

                    $message .= 'Inventario actualizado exitosamente. Lote inicial creado y registrado en Kardex.';
                } else {
                    $message = 'Inventario actualizado exitosamente.';
                }

                DB::commit();
                return ApiResponse::success([
                    'inventory' => $inventory->load('inventoryBatches.batch'),
                    'updated' => true,
                ], $message, 200);
            } else {
                DB::commit();
                return ApiResponse::success([
                    'inventory' => $inventory,
                    'updated' => false,
                ], 'No se realizaron cambios en el inventario', 200);
            }
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponse::error(null, 'Error de validación', 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponse::error('Inventario no encontrado', 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }




    /**
     * Get inventories with batches information for batches module
     */
    public function indexForBatches(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $warehouseId = $request->input('warehouse_id');

            $inventories = Inventory::with([
                'warehouse:id,name',
                'product:id,code,description',
            ])
                // Contar solo lotes con cantidad > 0 (disponibles)
                ->withCount(['inventoryBatches as batches_count' => function ($query) {
                    $query->where('quantity', '>', 0);
                }])
                // Sumar solo cantidades de lotes disponibles
                ->withSum(['inventoryBatches as stock_actual_quantity' => function ($query) {
                    $query->where('quantity', '>', 0);
                }], 'quantity')
                ->whereHas('product', function ($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });

            // Filtro por bodega
            if ($warehouseId) {
                $inventories->where('warehouse_id', $warehouseId);
            }

            $inventories = $inventories->paginate($perPage);

            // Transformar para incluir las relaciones explícitamente
            $inventories->getCollection()->transform(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'product_id' => $inventory->product_id,
                    'stock_actual_quantity' => $inventory->stock_actual_quantity ?? 0,
                    'stock_min' => $inventory->stock_min ?? 0,
                    'stock_max' => $inventory->stock_max ?? 0,
                    'is_active' => $inventory->is_active,
                    'batches_count' => $inventory->batches_count ?? 0,
                    'product' => $inventory->product ? [
                        'id' => $inventory->product->id,
                        'code' => $inventory->product->code,
                        'description' => $inventory->product->description,
                    ] : null,
                    'warehouse' => $inventory->warehouse ? [
                        'id' => $inventory->warehouse->id,
                        'name' => $inventory->warehouse->name,
                    ] : null,
                ];
            });

            return ApiResponse::success($inventories, 'Inventarios recuperados exitosamente', 200);
        } catch (\Exception $e) {
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
