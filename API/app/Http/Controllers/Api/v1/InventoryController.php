<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoryStoreRequest;
use App\Http\Requests\Api\v1\InventoryUpdateRequest;
use App\Http\Resources\Api\v1\InventoryCollection;
use App\Http\Resources\Api\v1\InventoryResource;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $inventories = Inventory::with('warehouse:id,name','product:id,code,original_code,description','prices')->paginate(10);
            return ApiResponse::success($inventories, 'Inventarios recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(InventoryStoreRequest $request): JsonResponse
    {
        try {
            $exists = Inventory::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->exists();
            if ($exists) {
                return ApiResponse::error(null,'Ya existe un inventario para este producto en este almacén', 400);
            }
            $inventory = (new Inventory)->create($request->validated());
            return ApiResponse::success($inventory, 'Inventario creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $inventory = Inventory::with('warehouse:id,name','product:id,code,original_code,description','prices')->findOrFail($id);
            return ApiResponse::success($inventory, 'Inventario recuperado exitosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('Inventario no encontrado','Inventario no encontrado', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(InventoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $inventory = Inventory::findOrFail($id);

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
                    'last_purchase',
                    'is_service'
                ])
            );

            return ApiResponse::success(new InventoryResource($inventory),'Inventario actualizado exitosamente',200);
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
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('Inventario no encontrado','Inventario no encontrado', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
