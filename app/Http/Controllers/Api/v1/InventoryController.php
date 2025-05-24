<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoryStoreRequest;
use App\Http\Requests\Api\v1\InventoryUpdateRequest;
use App\Http\Resources\Api\v1\InventoryCollection;
use App\Http\Resources\Api\v1\InventoryResource;
use App\Models\Inventory;
use App\Models\Price;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');

            $inventories = Inventory::with(
                ['warehouse:id,name',
                    'product:id,code,original_code,description,category_id,unit_measurement_id,image,barcode,description_measurement_id',
                    'prices',
                    'product.category',
                    'product.unitMeasurement'
                ])
                ->withSum('inventoryBatches', 'quantity')
                ->whereHas('product', function ($query) use ($search) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('original_code', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                })
                ->paginate($perPage);
            $inventories->getCollection()->transform(function ($inventory) {
                $stock = $inventory->inventoryBatches->sum('quantity');
                $inventory->actual_stock = number_format($stock ?? 0, 2);

                $price = $inventory->prices->firstWhere('is_default', 1)?->price ?? 0;
                $inventory->default_price = number_format($price, 2);

                return $inventory;
            });


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

    public function update(InventoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $existing = Inventory::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->where('id', '!=', $request->id) // si tienes ID al editar
                ->first();

            if ($existing) {
                return ApiResponse::error(null, 'Este producto ya existe en la sucursal que intentas levantarlo', 200);
            }

            $inventory = Inventory::updateOrCreate(
                [
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $request->product_id
                ],
                $request->only([
                    'last_cost_without_tax',
                    'last_cost_with_tax',
                    'stock_actual_quantity',
                    'stock_min',
                    'alert_stock_min',
                    'stock_max',
                    'alert_stock_max',
                    'provider_id',
                    'is_service',
                    'is_active'
                ])
            );

            // Actualizar o crear el inventario con la combinación única
            $inventory->updateOrCreate(
                [
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $request->product_id
                ],
                $request->only([
                    'last_cost_without_tax',
                    'last_cost_with_tax',
                    'stock_actual_quantity',
                    'stock_min',
                    'alert_stock_min',
                    'stock_max',
                    'alert_stock_max',
//                    'last_purchase',
                    'provider_id',
                    'is_service',
                    'is_active'
                ])
            );

            return ApiResponse::success(new InventoryResource($inventory), 'Inventario actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Inventario no encontrado', 'Inventario no encontrado', 404);
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
