<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\InventoriesBatch;
use App\Models\SaleItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class saleInventoryService
{
    /**
     * Descontar stock del lote especificado en el item de venta
     *
     * @param SaleItem $saleItem
     * @return bool
     * @throws Exception
     */
    public function decreaseStock(SaleItem $saleItem): bool
    {
        try {
            DB::beginTransaction();

            // Buscar el registro en inventories_batches
            $inventoryBatch = InventoriesBatch::where('id_inventory', $saleItem->inventory_id)
                ->where('id_batch', $saleItem->batch_id)
                ->first();

            if (!$inventoryBatch) {
                throw new Exception("No se encontró el lote {$saleItem->batch_id} para el inventario {$saleItem->inventory_id}");
            }

            // Verificar que haya suficiente stock
            if ($inventoryBatch->quantity < $saleItem->quantity) {
                throw new Exception("Stock insuficiente en el lote. Disponible: {$inventoryBatch->quantity}, Solicitado: {$saleItem->quantity}");
            }

            // Descontar del lote
            $inventoryBatch->quantity -= $saleItem->quantity;
            $inventoryBatch->save();

            // También actualizar el modelo Batch si es necesario
            $batch = Batch::find($saleItem->batch_id);
            if ($batch) {
                $batch->available_quantity -= $saleItem->quantity;
                $batch->save();
            }

            DB::commit();

            Log::info("Stock descontado", [
                'sale_item_id' => $saleItem->id,
                'inventory_id' => $saleItem->inventory_id,
                'batch_id' => $saleItem->batch_id,
                'quantity' => $saleItem->quantity,
                'remaining' => $inventoryBatch->quantity
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al descontar stock: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Incrementar stock al lote (usado en anulaciones)
     *
     * @param SaleItem $saleItem
     * @return bool
     * @throws Exception
     */
    public function increaseStock(SaleItem $saleItem): bool
    {
        try {
            DB::beginTransaction();

            // Buscar el registro en inventories_batches
            $inventoryBatch = InventoriesBatch::where('id_inventory', $saleItem->inventory_id)
                ->where('id_batch', $saleItem->batch_id)
                ->first();

            if (!$inventoryBatch) {
                throw new Exception("No se encontró el lote {$saleItem->batch_id} para el inventario {$saleItem->inventory_id}");
            }

            // Devolver al lote
            $inventoryBatch->quantity += $saleItem->quantity;
            $inventoryBatch->save();

            // También actualizar el modelo Batch
            $batch = Batch::find($saleItem->batch_id);
            if ($batch) {
                $batch->available_quantity += $saleItem->quantity;
                $batch->save();
            }

            DB::commit();

            Log::info("Stock devuelto", [
                'sale_item_id' => $saleItem->id,
                'inventory_id' => $saleItem->inventory_id,
                'batch_id' => $saleItem->batch_id,
                'quantity' => $saleItem->quantity,
                'new_total' => $inventoryBatch->quantity
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al devolver stock: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener el stock actual de un inventario sumando todos sus lotes
     *
     * @param int $inventoryId
     * @return float
     */
    public function getTotalStock(int $inventoryId): float
    {
        return InventoriesBatch::where('id_inventory', $inventoryId)
            ->sum('quantity');
    }

    /**
     * Obtener el stock de un lote específico
     *
     * @param int $inventoryId
     * @param int $batchId
     * @return float
     */
    public function getBatchStock(int $inventoryId, int $batchId): float
    {
        $inventoryBatch = InventoriesBatch::where('id_inventory', $inventoryId)
            ->where('id_batch', $batchId)
            ->first();

        return $inventoryBatch ? $inventoryBatch->quantity : 0;
    }
    /**

     * Asignar automáticamente el lote más antiguo (FIFO) a un item de venta

     *

     * @param SaleItem $saleItem

     * @return SaleItem

     * @throws Exception

     */

    public function assignOldestBatch(SaleItem $saleItem): SaleItem

    {

        try {
            // Buscar lotes disponibles ordenados por fecha (FIFO - primero el más antiguo)
            $availableBatches = InventoriesBatch::where('id_inventory', $saleItem->inventory_id)
                ->where('quantity', '>', 0)
                ->join('batches', 'inventories_batches.id_batch', '=', 'batches.id')
                ->orderBy('batches.incoming_date', 'asc') // FIFO: el más antiguo primero
                ->orderBy('inventories_batches.id', 'asc')
                ->select('inventories_batches.*')
                ->get();

            if ($availableBatches->isEmpty()) {
                throw new Exception("No hay lotes disponibles para el inventario {$saleItem->inventory_id}");
            }



            $remainingQuantity = $saleItem->quantity;

            $assignedBatchId = null;



            // Intentar asignar desde el lote más antiguo

            foreach ($availableBatches as $batch) {
                if ($batch->quantity >= $remainingQuantity) {
                    // Este lote tiene suficiente stock
                    $assignedBatchId = $batch->id_batch;
                    break;
                }
            }


            if (!$assignedBatchId) {
                // Ningún lote individual tiene suficiente stock
                $totalAvailable = $availableBatches->sum('quantity');
                throw new Exception("Stock insuficiente. Solicitado: {$saleItem->quantity}, Disponible: {$totalAvailable}");

            }


            // Asignar el batch_id al sale_item
            $saleItem->batch_id = $assignedBatchId;
            $saleItem->save();



            Log::info("Lote asignado automáticamente (FIFO)", [

                'sale_item_id' => $saleItem->id,

                'inventory_id' => $saleItem->inventory_id,

                'batch_id' => $assignedBatchId,

                'quantity' => $saleItem->quantity

            ]);



            return $saleItem;



        } catch (Exception $e) {

            Log::error("Error al asignar lote automáticamente: " . $e->getMessage());

            throw $e;

        }

    }
}