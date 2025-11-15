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
use App\Models\Kardex;
use App\Models\Batch;
use App\Models\BatchCodeOrigen;
use App\Helpers\KardexHelper;
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
     * Obtener lotes por inventario específico
     */
    public function getByInventory(Request $request, $inventoryId): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');

            $query = InventoriesBatch::with(['batch.origenCode'])
                ->where('id_inventory', $inventoryId);

            // Aplicar búsqueda si existe
            if (!empty($search)) {
                $query->whereHas('batch', function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('observations', 'like', "%{$search}%");
                });
            }

            $query->orderBy('created_at', 'desc');

            // Si se pasa per_page, paginar, sino devolver todo
            if ($request->has('per_page')) {
                $result = $query->paginate($perPage);
            } else {
                $result = $query->get();
            }

            return ApiResponse::success($result, 'Lotes del inventario recuperados', 200);
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

            $inventoryBatch = InventoriesBatch::with(['inventory.warehouse', 'inventory.product', 'batch'])->findOrFail($request->id_inventory_batch);
            $oldQuantity = $inventoryBatch->quantity;
            $difference = $request->quantity - $oldQuantity;

            // Actualizar cantidad del lote en inventories_batches
            $inventoryBatch->quantity = $request->quantity;
            $inventoryBatch->save();

            // Actualizar available_quantity en la tabla batches
            if ($inventoryBatch->batch) {
                $inventoryBatch->batch->available_quantity += $difference;
                $inventoryBatch->batch->save();
            }

            // Actualizar el inventario total
            $inventory = $inventoryBatch->inventory;
            $inventory->stock_actual_quantity += $difference;
            $inventory->save();

            // Registrar movimiento en Kardex
            if ($difference != 0) {
                $stockIn = $difference > 0 ? $difference : 0;
                $stockOut = $difference < 0 ? abs($difference) : 0;
                $newStockActual = $inventory->stock_actual_quantity;
                $previousStock = $newStockActual - $difference;

                KardexHelper::createKardexFromInventory(
                    $inventory->warehouse_id,
                    now()->toDateTimeString(),
                    'AJUSTE',
                    (string) $inventoryBatch->id,
                    (string) $inventoryBatch->id,
                    'AJUSTE',
                    'ADJ-' . $inventoryBatch->id,
                    'Sistema',
                    'N/A',
                    $inventory->id,
                    (int) $previousStock,
                    (int) $stockIn,
                    (int) $stockOut,
                    (int) $newStockActual,
                    0.0,
                    0.0,
                    0.0,
                    0.0,
                    0.0,
                    $inventoryBatch->id
                );
            }

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

            // Obtener historial de movimientos del Kardex relacionados con este lote
            $movements = Kardex::with(['inventory.product', 'warehouse'])
                ->where('inventory_batch_id', $id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'date' => $movement->date,
                        'operation_type' => $movement->operation_type,
                        'document_type' => $movement->document_type,
                        'document_number' => $movement->document_number,
                        'entity' => $movement->entity,
                        'type' => $movement->stock_in > 0 ? 'in' : 'out',
                        'quantity' => $movement->stock_in > 0 ? $movement->stock_in : $movement->stock_out,
                        'balance' => $movement->stock_actual,
                        'unit_cost' => $movement->stock_in > 0 ? $movement->purchase_price : $movement->sale_price,
                        'total_cost' => $movement->stock_in > 0 ? $movement->money_in : $movement->money_out,
                        'promedial_cost' => $movement->promedial_cost,
                        'reason' => $movement->operation_type,
                        'created_at' => $movement->created_at,
                    ];
                });

            return ApiResponse::success([
                'inventory_batch' => [
                    'id' => $inventoryBatch->id,
                    'quantity' => $inventoryBatch->quantity,
                    'batch' => $inventoryBatch->batch,
                    'product' => $inventoryBatch->inventory->product,
                    'warehouse' => $inventoryBatch->inventory->warehouse,
                ],
                'movements' => $movements,
                'summary' => [
                    'total_entries' => $movements->where('type', 'in')->count(),
                    'total_exits' => $movements->where('type', 'out')->count(),
                    'total_quantity_in' => $movements->where('type', 'in')->sum('quantity'),
                    'total_quantity_out' => $movements->where('type', 'out')->sum('quantity'),
                    'current_balance' => $inventoryBatch->quantity,
                ],
            ], 'Movimientos del lote recuperados exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al obtener movimientos', 500);
        }
    }

    /**
     * Crear lote manual con inventario inicial
     */
    public function createManualBatch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_inventory' => 'required|exists:inventories,id',
                'batch_code' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0',
                'incoming_date' => 'required|date',
                'expiration_date' => 'nullable|date|after:incoming_date',
                'unit_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Obtener el inventario
            $inventory = Inventory::with(['warehouse', 'product'])->findOrFail($request->id_inventory);

            // Obtener o crear el código de origen MANUAL
            $manualOriginCode = BatchCodeOrigen::firstOrCreate(
                ['code' => 'MANUAL'],
                ['description' => 'INGRESO MANUAL', 'is_active' => 1]
            );

            // Crear el lote en la tabla batches
            $batch = Batch::create([
                'code' => $request->batch_code,
                'purchase_item_id' => null, // Manual batch, not from purchase
                'origen_code' => $manualOriginCode->id,
                'inventory_id' => $request->id_inventory,
                'incoming_date' => $request->incoming_date,
                'expiration_date' => $request->expiration_date,
                'initial_quantity' => $request->quantity,
                'available_quantity' => $request->quantity,
                'observations' => $request->notes,
                'is_active' => true,
            ]);

            // Crear el registro en inventories_batches
            $inventoryBatch = InventoriesBatch::create([
                'id_inventory' => $request->id_inventory,
                'id_batch' => $batch->id,
                'quantity' => $request->quantity,
                'operation_date' => now(),
            ]);

            // Actualizar el stock total del inventario
            $previousStock = $inventory->stock_actual_quantity ?? 0;
            $inventory->stock_actual_quantity = $previousStock + $request->quantity;
            $inventory->save();

            // Registrar en el Kardex
            $unitCost = $request->unit_cost ?? 0;
            $totalCost = $request->quantity * $unitCost;

            KardexHelper::createKardexFromInventory(
                $inventory->warehouse_id,
                now()->toDateTimeString(),
                'INGRESO_MANUAL',
                (string) $batch->id,
                (string) $inventoryBatch->id,
                'INGRESO',
                'LOTE-' . $batch->code,
                'Ingreso Manual',
                'N/A',
                $inventory->id,
                (int) $previousStock,
                (int) $request->quantity,
                0,
                (int) ($previousStock + $request->quantity),
                (float) $totalCost,
                0.0,
                0.0,
                0.0,
                (float) $unitCost,
                $inventoryBatch->id
            );

            DB::commit();

            return ApiResponse::success([
                'batch' => $batch,
                'inventory_batch' => $inventoryBatch,
                'inventory' => $inventory,
            ], 'Lote creado exitosamente', 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al crear el lote', 500);
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

    /**
     * Eliminar un lote (solo lotes manuales sin movimientos)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $inventoryBatch = InventoriesBatch::with(['batch.origenCode'])->findOrFail($id);

            // Validar que el lote fue creado manualmente
            if (!$inventoryBatch->batch) {
                DB::rollBack();
                return ApiResponse::error(null, 'No se encontró el lote asociado', 404);
            }

            // Obtener el código de origen MANUAL
            $manualOriginCode = BatchCodeOrigen::where('code', 'MANUAL')->first();

            if (!$manualOriginCode || $inventoryBatch->batch->origen_code !== $manualOriginCode->id) {
                DB::rollBack();
                return ApiResponse::error(
                    null,
                    'Solo se pueden eliminar lotes creados manualmente. Este lote proviene de una compra u otro origen.',
                    403
                );
            }

            // Validar que no tenga movimientos en el Kardex
            $hasMovements = Kardex::where('inventory_batch_id', $inventoryBatch->id)->exists();

            if ($hasMovements) {
                DB::rollBack();
                return ApiResponse::error(
                    null,
                    'No se puede eliminar este lote porque ya tiene movimientos registrados en el Kardex.',
                    403
                );
            }

            // Actualizar el inventario total
            $inventory = $inventoryBatch->inventory;
            if ($inventory) {
                $inventory->stock_actual_quantity -= $inventoryBatch->quantity;
                $inventory->save();
            }

            // Eliminar el registro en inventories_batches
            $batchId = $inventoryBatch->id_batch;
            $inventoryBatch->delete();

            // Eliminar el lote en la tabla batches
            $batch = Batch::find($batchId);
            if ($batch) {
                $batch->delete();
            }

            DB::commit();

            return ApiResponse::success(null, 'Lote eliminado correctamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Lote no encontrado', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el lote', 500);
        }
    }
}
