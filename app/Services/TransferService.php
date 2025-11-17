<?php

namespace App\Services;

use App\Helpers\KardexHelper;
use App\Models\Batch;
use App\Models\Inventory;
use App\Models\InventoriesBatch;
use App\Models\Kardex;
use App\Models\Transfer;
use App\Models\TransferItem;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * Enviar/despachar un traslado (cambiar status a IN_TRANSIT)
     * - Reduce stock en warehouse origen
     * - Reduce cantidad en lotes origen
     * - Registra KARDEX de salida (TRANSFER_OUT)
     */
    public function sendTransfer(Transfer $transfer, int $userId): void
    {
        DB::transaction(function () use ($transfer, $userId) {
            // Validar que el transfer está en status PENDING
            if ($transfer->status !== 'PENDING') {
                throw new \Exception('Solo se pueden enviar traslados en estado PENDING');
            }

            // Actualizar status del transfer
            $transfer->update([
                'status' => 'IN_TRANSIT',
                'sent_by' => $userId,
                'sent_at' => now()
            ]);

            // Procesar cada item
            foreach ($transfer->items as $item) {
                $this->sendTransferItem($item);
            }
        });
    }

    /**
     * Procesar el envío de un item individual
     */
    private function sendTransferItem(TransferItem $item): void
    {
        // 1. Reducir cantidad del lote en origen
        $batch = Batch::findOrFail($item->batch_id);

        if ($batch->available_quantity < $item->quantity) {
            throw new \Exception("Stock insuficiente en lote {$batch->code}");
        }

        $batch->decrement('available_quantity', $item->quantity);

        // 2. Reducir stock_actual_quantity del inventario origen
        $inventoryOrigin = Inventory::findOrFail($item->inventory_origin_id);
        $previousStock = $inventoryOrigin->stock_actual_quantity;
        $inventoryOrigin->decrement('stock_actual_quantity', $item->quantity);

        // 3. Obtener el último costo promedio para el cálculo de money_actual
        $lastKardex = Kardex::where('inventory_id', $inventoryOrigin->id)
            ->orderByDesc('id')
            ->first();

        $promedialCost = $lastKardex->promedial_cost ?? 0;

        // 4. Obtener inventory_batch_id de la tabla pivot
        $inventoryBatch = InventoriesBatch::where('id_inventory', $item->inventory_origin_id)
            ->where('id_batch', $item->batch_id)
            ->first();

        // 5. Registrar KARDEX de SALIDA en warehouse origen
        KardexHelper::createKardexFromInventory(
            branch_id: $inventoryOrigin->warehouse_id,
            date: now(),
            operation_type: 'TRANSFER_OUT',
            operation_id: $item->transfer_id,
            operation_detail_id: $item->id,
            document_type: 'TRASLADO',
            document_number: $item->transfer->transfer_number,
            entity: "Traslado a " . $item->transfer->warehouseDestination->name,
            nationality: 'N/A',
            inventory_id: $item->inventory_origin_id,
            previous_stock: $previousStock,
            stock_in: 0,
            stock_out: $item->quantity,
            stock_actual: $inventoryOrigin->stock_actual_quantity,
            money_in: 0,
            money_out: $item->quantity * $item->unit_cost,
            money_actual: $inventoryOrigin->stock_actual_quantity * $promedialCost,
            sale_price: 0,
            purchase_price: $item->unit_cost,
            inventory_batch_id: $inventoryBatch ? $inventoryBatch->id : null
        );

        // 5. Actualizar status del item
        $item->update(['status' => 'SENT']);
    }

    /**
     * Recibir un traslado (cambiar status a RECEIVED)
     * - Crea nuevo lote en warehouse destino
     * - Incrementa stock en warehouse destino
     * - Registra KARDEX de entrada (TRANSFER_IN)
     */
    public function receiveTransfer(Transfer $transfer, int $userId): void
    {
        DB::transaction(function () use ($transfer, $userId) {
            // Validar que el transfer está en status IN_TRANSIT
            if ($transfer->status !== 'IN_TRANSIT') {
                throw new \Exception('Solo se pueden recibir traslados en estado IN_TRANSIT');
            }

            // Actualizar status del transfer
            $transfer->update([
                'status' => 'RECEIVED',
                'received_by' => $userId,
                'received_at' => now()
            ]);

            // Procesar cada item
            foreach ($transfer->items as $item) {
                $this->receiveTransferItem($item);
            }
        });
    }

    /**
     * Procesar la recepción de un item individual
     */
    private function receiveTransferItem(TransferItem $item): void
    {
        $originalBatch = Batch::findOrFail($item->batch_id);

        // 1. Crear nuevo lote en warehouse destino vinculado al mismo purchase_item
        $newBatch = Batch::create([
            'code' => "TRANS-{$item->transfer->transfer_number}-{$originalBatch->code}",
            'purchase_item_id' => $originalBatch->purchase_item_id,
            'origen_code' => 2, // Código para "TRASLADO"
            'inventory_id' => $item->inventory_destination_id,
            'incoming_date' => now(),
            'expiration_date' => $originalBatch->expiration_date,
            'initial_quantity' => $item->quantity,
            'available_quantity' => $item->quantity,
            'observations' => "Recibido de traslado {$item->transfer->transfer_number}",
            'is_active' => true
        ]);

        // 2. Crear relación inventories_batches
        $newInventoryBatch = InventoriesBatch::create([
            'id_inventory' => $item->inventory_destination_id,
            'id_batch' => $newBatch->id,
            'quantity' => $item->quantity,
            'operation_date' => now()
        ]);

        // 3. Incrementar stock_actual_quantity del inventario destino
        $inventoryDestination = Inventory::findOrFail($item->inventory_destination_id);
        $previousStock = $inventoryDestination->stock_actual_quantity;
        $inventoryDestination->increment('stock_actual_quantity', $item->quantity);

        // 4. Obtener el último costo promedio ANTES de la entrada para previous money_actual
        $lastKardex = Kardex::where('inventory_id', $inventoryDestination->id)
            ->orderByDesc('id')
            ->first();

        $previousPromedialCost = $lastKardex->promedial_cost ?? 0;

        // 5. Calcular nuevo costo promedio ponderado
        $totalQuantityBefore = $previousStock;
        $totalCostBefore = $totalQuantityBefore * $previousPromedialCost;

        $totalQuantityAfter = $previousStock + $item->quantity;
        $totalCostAfter = $totalCostBefore + ($item->quantity * $item->unit_cost);

        $newPromedialCost = $totalQuantityAfter > 0 ? $totalCostAfter / $totalQuantityAfter : 0;

        // 6. Registrar KARDEX de ENTRADA en warehouse destino
        KardexHelper::createKardexFromInventory(
            branch_id: $inventoryDestination->warehouse_id,
            date: now(),
            operation_type: 'TRANSFER_IN',
            operation_id: $item->transfer_id,
            operation_detail_id: $item->id,
            document_type: 'TRASLADO',
            document_number: $item->transfer->transfer_number,
            entity: "Traslado desde " . $item->transfer->warehouseOrigin->name,
            nationality: 'N/A',
            inventory_id: $item->inventory_destination_id,
            previous_stock: $previousStock,
            stock_in: $item->quantity,
            stock_out: 0,
            stock_actual: $inventoryDestination->stock_actual_quantity,
            money_in: $item->quantity * $item->unit_cost,
            money_out: 0,
            money_actual: $inventoryDestination->stock_actual_quantity * $newPromedialCost,
            sale_price: 0,
            purchase_price: $item->unit_cost,
            inventory_batch_id: $newInventoryBatch->id
        );

        // 7. Actualizar status del item
        $item->update(['status' => 'RECEIVED']);
    }

    /**
     * Cancelar un traslado (solo si está en PENDING)
     */
    public function cancelTransfer(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'PENDING') {
                throw new \Exception('Solo se pueden cancelar traslados en estado PENDING');
            }

            $transfer->update(['status' => 'CANCELLED']);

            // Actualizar status de todos los items
            $transfer->items()->update(['status' => 'CANCELLED']);
        });
    }

    /**
     * Obtener lotes disponibles para un producto en un warehouse
     */
    public function getAvailableBatches(int $inventoryId): array
    {
        $inventory = Inventory::with(['inventoryBatches.batch.purchaseItem'])
            ->findOrFail($inventoryId);

        $batches = [];

        foreach ($inventory->inventoryBatches as $inventoryBatch) {
            $batch = $inventoryBatch->batch;

            if ($batch && $batch->is_active && $batch->available_quantity > 0) {
                $batches[] = [
                    'batch_id' => $batch->id,
                    'batch_code' => $batch->code,
                    'available_quantity' => $batch->available_quantity,
                    'expiration_date' => $batch->expiration_date,
                    'unit_cost' => $batch->purchaseItem->price ?? 0,
                ];
            }
        }

        return $batches;
    }
}
