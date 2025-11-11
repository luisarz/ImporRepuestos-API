<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoriesBatchStoreRequest;
use App\Http\Requests\Api\v1\InventoriesBatchUpdateRequest;
use App\Http\Resources\Api\v1\InventoriesBatchCollection;
use App\Http\Resources\Api\v1\InventoriesBatchResource;
use App\Models\InventoriesBatch;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventoriesBatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $query = InventoriesBatch::with(['inventory.product', 'inventory.warehouse', 'batch']);

            // Filtros
            if ($request->has('warehouse_id')) {
                $query->whereHas('inventory', function($q) use ($request) {
                    $q->where('id_warehouse', $request->input('warehouse_id'));
                });
            }

            if ($request->has('product_id')) {
                $query->whereHas('inventory', function($q) use ($request) {
                    $q->where('id_product', $request->input('product_id'));
                });
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                $today = Carbon::now();

                if ($status === 'expired') {
                    $query->whereHas('batch', function($q) use ($today) {
                        $q->whereDate('expiration_date', '<', $today);
                    });
                } elseif ($status === 'expiring') {
                    $expiringDate = $today->copy()->addDays(30);
                    $query->whereHas('batch', function($q) use ($today, $expiringDate) {
                        $q->whereDate('expiration_date', '>=', $today)
                          ->whereDate('expiration_date', '<=', $expiringDate);
                    });
                } elseif ($status === 'valid') {
                    $expiringDate = $today->copy()->addDays(30);
                    $query->whereHas('batch', function($q) use ($expiringDate) {
                        $q->whereDate('expiration_date', '>', $expiringDate);
                    });
                }
            }

            $inventoriesBatches = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return ApiResponse::success($inventoriesBatches, 'Movimiento de lotes e inventario recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener inventarios por almacén
     */
    public function getByWarehouse(Request $request, $warehouseId): JsonResponse
    {
        try {
            $inventoriesBatches = InventoriesBatch::with(['inventory.product', 'batch'])
                ->whereHas('inventory', function($q) use ($warehouseId) {
                    $q->where('id_warehouse', $warehouseId);
                })
                ->where('quantity', '>', 0)
                ->get();

            return ApiResponse::success($inventoriesBatches, 'Inventarios por almacén', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener inventarios por producto
     */
    public function getByProduct(Request $request, $productId): JsonResponse
    {
        try {
            $inventoriesBatches = InventoriesBatch::with(['inventory.warehouse', 'batch'])
                ->whereHas('inventory', function($q) use ($productId) {
                    $q->where('id_product', $productId);
                })
                ->where('quantity', '>', 0)
                ->get();

            return ApiResponse::success($inventoriesBatches, 'Inventarios por producto', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener lotes próximos a vencer
     */
    public function getExpiring(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $today = Carbon::now();
            $expiringDate = $today->copy()->addDays($days);

            $inventoriesBatches = InventoriesBatch::with(['inventory.product', 'inventory.warehouse', 'batch'])
                ->whereHas('batch', function($q) use ($today, $expiringDate) {
                    $q->whereDate('expiration_date', '>=', $today)
                      ->whereDate('expiration_date', '<=', $expiringDate);
                })
                ->where('quantity', '>', 0)
                ->orderBy(DB::raw('(SELECT expiration_date FROM batches WHERE id = inventories_batches.id_batch)'))
                ->get();

            return ApiResponse::success($inventoriesBatches, "Lotes que vencen en {$days} días", 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener lotes vencidos
     */
    public function getExpired(Request $request): JsonResponse
    {
        try {
            $today = Carbon::now();

            $inventoriesBatches = InventoriesBatch::with(['inventory.product', 'inventory.warehouse', 'batch'])
                ->whereHas('batch', function($q) use ($today) {
                    $q->whereDate('expiration_date', '<', $today);
                })
                ->where('quantity', '>', 0)
                ->orderBy(DB::raw('(SELECT expiration_date FROM batches WHERE id = inventories_batches.id_batch)'), 'desc')
                ->get();

            return ApiResponse::success($inventoriesBatches, 'Lotes vencidos', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Transferir inventario entre almacenes
     */
    public function transfer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_batch' => 'required|exists:batches,id',
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'quantity' => 'required|numeric|min:0.01',
                'id_product' => 'required|exists:products,id',
            ]);

            DB::beginTransaction();

            // Obtener inventario origen
            $fromInventory = Inventory::where('id_product', $request->id_product)
                ->where('id_warehouse', $request->from_warehouse_id)
                ->firstOrFail();

            // Obtener o crear inventario destino
            $toInventory = Inventory::firstOrCreate(
                [
                    'id_product' => $request->id_product,
                    'id_warehouse' => $request->to_warehouse_id,
                ],
                [
                    'quantity' => 0,
                    'minimum_stock' => $fromInventory->minimum_stock ?? 0,
                ]
            );

            // Obtener el lote en el inventario origen
            $fromBatch = InventoriesBatch::where('id_inventory', $fromInventory->id)
                ->where('id_batch', $request->id_batch)
                ->firstOrFail();

            // Validar que hay suficiente cantidad
            if ($fromBatch->quantity < $request->quantity) {
                DB::rollBack();
                return ApiResponse::error(null, 'Cantidad insuficiente en el lote origen', 400);
            }

            // Restar del inventario origen
            $fromBatch->quantity -= $request->quantity;
            $fromBatch->save();

            $fromInventory->quantity -= $request->quantity;
            $fromInventory->save();

            // Agregar al inventario destino
            $toBatch = InventoriesBatch::firstOrCreate(
                [
                    'id_inventory' => $toInventory->id,
                    'id_batch' => $request->id_batch,
                ],
                [
                    'quantity' => 0,
                    'movement_type' => 'TRANSFER_IN',
                ]
            );

            $toBatch->quantity += $request->quantity;
            $toBatch->movement_type = 'TRANSFER_IN';
            $toBatch->save();

            $toInventory->quantity += $request->quantity;
            $toInventory->save();

            DB::commit();

            return ApiResponse::success([
                'from_batch' => $fromBatch,
                'to_batch' => $toBatch,
            ], 'Transferencia realizada exitosamente', 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error en la transferencia', 500);
        }
    }

    /**
     * Ajustar stock de un lote
     */
    public function adjustStock(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_inventory_batch' => 'required|exists:inventories_batches,id',
                'quantity' => 'required|numeric',
                'reason' => 'required|string|max:255',
            ]);

            DB::beginTransaction();

            $inventoryBatch = InventoriesBatch::with('inventory')->findOrFail($request->id_inventory_batch);
            $oldQuantity = $inventoryBatch->quantity;
            $difference = $request->quantity - $oldQuantity;

            // Actualizar cantidad del lote
            $inventoryBatch->quantity = $request->quantity;
            $inventoryBatch->movement_type = 'ADJUSTMENT';
            $inventoryBatch->save();

            // Actualizar el inventario total
            $inventory = $inventoryBatch->inventory;
            $inventory->quantity += $difference;
            $inventory->save();

            // Aquí podrías registrar el movimiento en una tabla de auditoría si existe
            // MovementLog::create([...]);

            DB::commit();

            return ApiResponse::success([
                'inventory_batch' => $inventoryBatch,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $request->quantity,
                'difference' => $difference,
                'reason' => $request->reason,
            ], 'Ajuste de stock realizado exitosamente', 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error en el ajuste de stock', 500);
        }
    }

    /**
     * Obtener movimientos de un lote
     */
    public function getMovements(Request $request, $id): JsonResponse
    {
        try {
            // Obtener el lote de inventario
            $inventoryBatch = InventoriesBatch::with(['inventory.product', 'inventory.warehouse', 'batch'])
                ->findOrFail($id);

            // Obtener historial de movimientos relacionados
            // Esto podría incluir ventas, compras, transferencias, ajustes, etc.
            $movements = InventoriesBatch::with(['inventory.warehouse', 'batch'])
                ->where('id_batch', $inventoryBatch->id_batch)
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponse::success([
                'inventory_batch' => $inventoryBatch,
                'movements' => $movements,
            ], 'Movimientos del lote', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(InventoriesBatchStoreRequest $request): JsonResponse
    {
        try {
            $inventoriesBatch = (new InventoriesBatch)->create($request->validated());
            return ApiResponse::success($inventoriesBatch, 'Movimiento de inventario por lote creado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $inventoriesBatch = (new InventoriesBatch)->findOrFail($id);
            return ApiResponse::success($inventoriesBatch, 'Movimiento de inventario por lote recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Movimiento no encontrad', 404);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }
    }

//    public function update(InventoriesBatchUpdateRequest $request, InventoriesBatch $inventoriesBatch): Response
//    {
//        $inventoriesBatch->update($request->validated());
//
//        return new InventoriesBatchResource($inventoriesBatch);
//    }

//    public function destroy(Request $request, InventoriesBatch $inventoriesBatch): Response
//    {
//        $inventoriesBatch->delete();
//
//        return response()->noContent();
//    }
}
