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
use Illuminate\Validation\ValidationException;

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
            $is_temp=false;
            if($inventory->is_temp == 0){
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
                $lote=new Batch();
                $lote->code=$inventory->product->code;
                $lote->origen_code=1;
                $lote->inventory_id=$inventory->id;
                $lote->incoming_date=now();
                $lote->expiration_date=null;
                $lote->initial_quantity=$request->stock_actual_quantity;
                $lote->available_quantity=$request->stock_actual_quantity;
                $lote->observations="Lote de Iniventario inicial";
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
