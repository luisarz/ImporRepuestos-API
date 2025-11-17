<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PurchasesHeaderStoreRequest;
use App\Http\Requests\Api\v1\PurchasesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\PurchasesHeaderCollection;
use App\Http\Resources\Api\v1\PurchasesHeaderResource;
use App\Models\PurchasesHeader;
use App\Models\Inventory;
use App\Models\Batch;
use App\Models\InventoriesBatch;
use App\Services\KardexService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchasesHeaderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $query = PurchasesHeader::with(
                [
                    'warehouse:id,name,address,phone,email',
                    'quotePurchase',
                    'provider:id,comercial_name,document_number,payment_type_id'
                ]
            );

            // Filtro por rango de fechas
            if ($request->has('date_from')) {
                $query->whereDate('purchase_date', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('purchase_date', '<=', $request->input('date_to'));
            }

            // Filtro por estado
            if ($request->has('status_filter') && $request->input('status_filter') !== '') {
                $query->where('status_purchase', $request->input('status_filter'));
            }

            $purchasesHeaders = $query->orderBy('purchase_date', 'desc')->paginate($perPage);
            return ApiResponse::success($purchasesHeaders, 'Compras cargadas', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(PurchasesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $purchasesHeader = (new PurchasesHeader)->create($request->validated());
            return ApiResponse::success($purchasesHeader, 'Compra Iniciada exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new PurchasesHeader)->findOrFail($id);
            return ApiResponse::success($purchasesHeader, 'Cabecera de compra recuperada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'La cabecera de compra no existe');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar el header de la compra');
        }
    }

    public function update(PurchasesHeaderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new \App\Models\PurchasesHeader)->findOrFail($id);
            $purchasesHeader->update($request->validated());
            return ApiResponse::success($purchasesHeader, 'Header de compra Modificada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Cabecera de compra no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al modificar el header de compra', 500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new \App\Models\PurchasesHeader)->findOrFail($id);
            $purchasesHeader->delete();
            return ApiResponse::success($purchasesHeader, 'Header de compra eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Cabecera de compra no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al eliminar el header de compra', 500);
        }
    }

    /**
     * Obtener estadísticas de compras
     */
    public function stats(): JsonResponse
    {
        try {
            $total = PurchasesHeader::count();
            $pending = PurchasesHeader::where('status_purchase', '1')->count(); // Procesando
            $completed = PurchasesHeader::where('status_purchase', '2')->count(); // Finalizada
            $cancelled = PurchasesHeader::where('status_purchase', '3')->count(); // Anulada

            $stats = [
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed,
                'cancelled' => $cancelled
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al obtener estadísticas', 500);
        }
    }

    /**
     * Recibir compra (marcar como finalizada)
     * Registra en Kardex y actualiza inventario disponible
     */
    public function receive(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $purchase = PurchasesHeader::with(['purchaseItems.batches', 'provider'])->findOrFail($id);

            // Validar que esté en estado "Procesando"
            if ($purchase->status_purchase != '1') {
                return ApiResponse::error(null, 'Solo se pueden recibir compras en estado "Procesando"', 400);
            }

            // Inicializar servicio de Kardex
            $kardexService = new KardexService();

            // Procesar cada item de compra
            foreach ($purchase->purchaseItems as $purchaseItem) {
                // Obtener el batch asociado
                $batch = Batch::where('purchase_item_id', $purchaseItem->id)->first();

                if (!$batch) {
                    throw new \Exception("No se encontró batch para el item de compra {$purchaseItem->id}. Asegúrese de que el batch fue creado correctamente.");
                }

                // Validar que el batch tenga inventory_id
                if (!$batch->inventory_id) {
                    throw new \Exception("El batch ID {$batch->id} no tiene inventory_id asignado. Datos inconsistentes.");
                }

                // Registrar movimiento en Kardex
                $kardex = $kardexService->registerPurchaseMovement($purchaseItem, $purchase);

                // Actualizar cantidad disponible en el batch
                $batch->available_quantity = ($batch->available_quantity ?? 0) + $purchaseItem->quantity;
                $batch->save();

                // Crear o actualizar el registro en inventories_batches
                $inventoryBatch = InventoriesBatch::firstOrCreate(
                    [
                        'id_inventory' => $batch->inventory_id,
                        'id_batch' => $batch->id,
                    ],
                    [
                        'quantity' => 0,
                        'operation_date' => now(),
                    ]
                );

                // Incrementar la cantidad en el inventario batch
                $inventoryBatch->quantity += $purchaseItem->quantity;
                $inventoryBatch->save();

                Log::info("Batch actualizado", [
                    'batch_id' => $batch->id,
                    'inventory_id' => $batch->inventory_id,
                    'quantity_added' => $purchaseItem->quantity,
                    'new_available' => $batch->available_quantity,
                    'inventory_batch_id' => $inventoryBatch->id,
                    'inventory_batch_quantity' => $inventoryBatch->quantity
                ]);
            }

            // Actualizar estado a "Finalizada"
            $purchase->status_purchase = '2';
            $purchase->save();

            DB::commit();

            Log::info("Compra recibida exitosamente", [
                'purchase_id' => $purchase->id,
                'items_count' => $purchase->purchaseItems->count()
            ]);

            return ApiResponse::success($purchase, 'Compra recibida exitosamente. Inventario y Kardex actualizados.', 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("Compra no encontrada: " . $e->getMessage());
            return ApiResponse::error(null, 'Compra no encontrada', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al recibir la compra: " . $e->getMessage(), [
                'purchase_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recibir la compra', 500);
        }
    }

    /**
     * Aprobar múltiples compras
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:purchases_headers,id'
            ]);

            $updated = PurchasesHeader::whereIn('id', $request->ids)
                ->where('status_purchase', '1')
                ->update(['status_purchase' => '2']);

            return ApiResponse::success([
                'updated_count' => $updated,
                'ids' => $request->ids
            ], "{$updated} compras aprobadas exitosamente", 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al aprobar compras', 500);
        }
    }

    /**
     * Cancelar una compra individual
     * Registra en Kardex y actualiza inventario si la compra estaba finalizada
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $purchase = PurchasesHeader::with(['purchaseItems.batches', 'provider'])->findOrFail($id);

            // Validar que no esté ya cancelada
            if ($purchase->status_purchase == '3') {
                return ApiResponse::error(null, 'Esta compra ya está cancelada', 400);
            }

            $kardexService = new KardexService();

            // Si la compra ya fue finalizada (estado '2'), necesitamos revertir kardex e inventario
            if ($purchase->status_purchase == '2') {
                foreach ($purchase->purchaseItems as $purchaseItem) {
                    // Obtener el batch asociado
                    $batch = Batch::where('purchase_item_id', $purchaseItem->id)->first();

                    if (!$batch) {
                        throw new \Exception("No se encontró batch para el item de compra {$purchaseItem->id}");
                    }

                    // Validar que haya stock suficiente en el lote para revertir
                    if ($batch->available_quantity < $purchaseItem->quantity) {
                        throw new \Exception("No hay stock suficiente en el lote para cancelar esta compra. Stock del lote: {$batch->available_quantity}, requerido: {$purchaseItem->quantity}");
                    }

                    // Registrar anulación en kardex
                    $kardexService->registerPurchaseCancellation($purchaseItem, $purchase);

                    // Restar cantidad del batch
                    $batch->available_quantity -= $purchaseItem->quantity;
                    $batch->save();

                    // Restar cantidad del inventories_batches
                    $inventoryBatch = InventoriesBatch::where('id_inventory', $batch->inventory_id)
                        ->where('id_batch', $batch->id)
                        ->first();

                    if ($inventoryBatch) {
                        $inventoryBatch->quantity -= $purchaseItem->quantity;

                        // Si la cantidad llega a 0 o menos, eliminar el registro
                        if ($inventoryBatch->quantity <= 0) {
                            $inventoryBatch->delete();
                        } else {
                            $inventoryBatch->save();
                        }
                    }

                    Log::info("Batch revertido por cancelación", [
                        'batch_id' => $batch->id,
                        'quantity_reverted' => $purchaseItem->quantity,
                        'new_available' => $batch->available_quantity,
                        'inventory_batch_updated' => $inventoryBatch ? true : false
                    ]);
                }
            }

            // Actualizar estado a "Anulada"
            $purchase->status_purchase = '3';
            $purchase->save();

            DB::commit();

            Log::info("Compra cancelada exitosamente", [
                'purchase_id' => $purchase->id,
                'was_finalized' => $purchase->status_purchase == '2'
            ]);

            return ApiResponse::success($purchase, 'Compra cancelada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponse::error(null, 'Compra no encontrada', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cancelar compra: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'Error al cancelar la compra', 500);
        }
    }

    /**
     * Cancelar múltiples compras
     * Registra en Kardex y actualiza inventario para compras finalizadas
     */
    public function bulkCancel(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:purchases_headers,id'
            ]);

            $kardexService = new KardexService();
            $cancelledCount = 0;

            // Procesar cada compra individualmente para manejar kardex
            foreach ($request->ids as $purchaseId) {
                $purchase = PurchasesHeader::with(['purchaseItems.batches', 'provider'])
                    ->whereIn('status_purchase', ['1', '2'])
                    ->find($purchaseId);

                if (!$purchase) {
                    continue; // Saltar si no existe o ya está cancelada
                }

                // Si la compra ya fue finalizada (estado '2'), necesitamos revertir kardex e inventario
                if ($purchase->status_purchase == '2') {
                    foreach ($purchase->purchaseItems as $purchaseItem) {
                        // Registrar anulación en kardex
                        $kardexService->registerPurchaseCancellation($purchaseItem, $purchase);

                        // Obtener el batch asociado
                        $batch = Batch::where('purchase_item_id', $purchaseItem->id)->first();

                        if ($batch) {
                            // Restar cantidad del batch
                            $batch->available_quantity = ($batch->available_quantity ?? 0) - $purchaseItem->quantity;
                            $batch->save();
                        }
                    }
                }

                // Actualizar estado a "Anulada"
                $purchase->status_purchase = '3';
                $purchase->save();
                $cancelledCount++;
            }

            DB::commit();

            return ApiResponse::success([
                'updated_count' => $cancelledCount,
                'ids' => $request->ids
            ], "{$cancelledCount} compras canceladas exitosamente", 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cancelar compras: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'Error al cancelar compras', 500);
        }
    }

    /**
     * Obtener historial de Kardex de una compra
     * Retorna todos los movimientos kardex asociados a esta compra
     */
    public function kardexHistory(Request $request, $id): JsonResponse
    {
        try {
            $purchase = PurchasesHeader::findOrFail($id);

            // Obtener todos los movimientos de kardex relacionados con esta compra
            $kardexEntries = \App\Models\Kardex::where('operation_type', 'PURCHASE')
                ->where('operation_id', $purchase->id)
                ->with(['inventory.product', 'branch'])
                ->orderBy('date', 'desc')
                ->get();

            // También obtener movimientos de anulación si existen
            $cancellationEntries = \App\Models\Kardex::where('operation_type', 'PURCHASE_CANCELLATION')
                ->where('operation_id', $purchase->id)
                ->with(['inventory.product', 'branch'])
                ->orderBy('date', 'desc')
                ->get();

            $allEntries = $kardexEntries->merge($cancellationEntries)->sortByDesc('date');

            return ApiResponse::success([
                'purchase_id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'status' => $purchase->status_purchase,
                'kardex_entries' => $allEntries->values(),
                'total_entries' => $allEntries->count()
            ], 'Historial de kardex recuperado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Compra no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener historial de kardex', 500);
        }
    }

    /**
     * Eliminar múltiples compras
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:purchases_headers,id'
            ]);

            $deleted = PurchasesHeader::whereIn('id', $request->ids)->delete();

            return ApiResponse::success([
                'deleted_count' => $deleted,
                'ids' => $request->ids
            ], "{$deleted} compras eliminadas exitosamente", 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar compras', 500);
        }
    }
}
