<?php

namespace App\Services;

use App\Models\Kardex;
use App\Models\SaleItem;
use App\Models\SalesHeader;
use App\Models\InventoriesBatch;
use App\Models\PurchaseItem;
use App\Models\PurchasesHeader;
use App\Models\Batch;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KardexService
{
    /**
     * Registrar salida de inventario por venta
     *
     * @param SaleItem $saleItem
     * @return Kardex
     * @throws Exception
     */
    public function registerSaleMovement(SaleItem $saleItem): Kardex
    {
        try {
            $sale = SalesHeader::with(['customer', 'warehouse', 'documentType'])
                ->findOrFail($saleItem->sale_id);

            // Obtener el stock previo del inventario
            $previousKardex = $this->getLastKardexEntry($saleItem->inventory_id, $sale->warehouse_id);

            $previousStock = $previousKardex ? $previousKardex->stock_actual : 0;
            $previousMoney = $previousKardex ? $previousKardex->money_actual : 0;
            $previousCost = $previousKardex ? $previousKardex->promedial_cost : 0;

            // Calcular nuevos valores
            $stockOut = $saleItem->quantity;
            $stockActual = $previousStock - $stockOut;

            // Calcular dinero (basado en costo promedio)
            $moneyOut = $stockOut * $previousCost;
            $moneyActual = $previousMoney - $moneyOut;

            // Obtener el inventory_batch_id si existe
            $inventoryBatchId = null;
            if ($saleItem->batch_id && $saleItem->inventory_id) {
                $inventoryBatch = InventoriesBatch::where('id_batch', $saleItem->batch_id)
                    ->where('id_inventory', $saleItem->inventory_id)
                    ->first();
                $inventoryBatchId = $inventoryBatch?->id;
            }

            // Crear entrada en kardex
            $kardex = Kardex::create([
                'branch_id' => $sale->warehouse_id,
                'date' => now(), // Usar hora actual del sistema para registrar el momento exacto del movimiento
                'operation_type' => 'SALE',
                'operation_id' => $sale->id,
                'operation_detail_id' => $saleItem->id,
                'document_type' => $sale->documentType?->name ?? 'VENTA',
                'document_number' => $sale->document_internal_number,
                'entity' => $sale->customer?->name ?? 'CLIENTE GENERICO',
                'nationality' => $sale->customer?->nationality ?? 'NACIONAL',
                'inventory_id' => $saleItem->inventory_id,
                'inventory_batch_id' => $inventoryBatchId,
                'previous_stock' => $previousStock,
                'stock_in' => 0,
                'stock_out' => $stockOut,
                'stock_actual' => $stockActual,
                'money_in' => 0,
                'money_out' => $moneyOut,
                'money_actual' => $moneyActual,
                'sale_price' => $saleItem->price,
                'purchase_price' => $previousCost,
                'promedial_cost' => $previousCost,
            ]);

            Log::info("Kardex registrado - SALE", [
                'kardex_id' => $kardex->id,
                'sale_id' => $sale->id,
                'inventory_id' => $saleItem->inventory_id,
                'stock_out' => $stockOut,
                'stock_actual' => $stockActual
            ]);

            return $kardex;

        } catch (Exception $e) {
            Log::error("Error al registrar kardex SALE: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Registrar entrada de inventario por anulación de venta
     *
     * @param SaleItem $saleItem
     * @return Kardex
     * @throws Exception
     */
    public function registerSaleCancellation(SaleItem $saleItem): Kardex
    {
        try {
            $sale = SalesHeader::with(['customer', 'warehouse', 'documentType'])
                ->findOrFail($saleItem->sale_id);

            // Obtener el stock previo del inventario
            $previousKardex = $this->getLastKardexEntry($saleItem->inventory_id, $sale->warehouse_id);

            $previousStock = $previousKardex ? $previousKardex->stock_actual : 0;
            $previousMoney = $previousKardex ? $previousKardex->money_actual : 0;
            $previousCost = $previousKardex ? $previousKardex->promedial_cost : 0;

            // Calcular nuevos valores
            $stockIn = $saleItem->quantity;
            $stockActual = $previousStock + $stockIn;

            // Calcular dinero (basado en costo promedio)
            $moneyIn = $stockIn * $previousCost;
            $moneyActual = $previousMoney + $moneyIn;

            // Obtener el inventory_batch_id si existe
            $inventoryBatchId = null;
            if ($saleItem->batch_id && $saleItem->inventory_id) {
                $inventoryBatch = InventoriesBatch::where('id_batch', $saleItem->batch_id)
                    ->where('id_inventory', $saleItem->inventory_id)
                    ->first();
                $inventoryBatchId = $inventoryBatch?->id;
            }

            // Crear entrada en kardex
            $kardex = Kardex::create([
                'branch_id' => $sale->warehouse_id,
                'date' => now(),
                'operation_type' => 'SALE_CANCELLATION',
                'operation_id' => $sale->id,
                'operation_detail_id' => $saleItem->id,
                'document_type' => 'ANULACION ' . ($sale->documentType?->name ?? 'VENTA'),
                'document_number' => $sale->document_internal_number,
                'entity' => $sale->customer?->name ?? 'CLIENTE GENERICO',
                'nationality' => $sale->customer?->nationality ?? 'NACIONAL',
                'inventory_id' => $saleItem->inventory_id,
                'inventory_batch_id' => $inventoryBatchId,
                'previous_stock' => $previousStock,
                'stock_in' => $stockIn,
                'stock_out' => 0,
                'stock_actual' => $stockActual,
                'money_in' => $moneyIn,
                'money_out' => 0,
                'money_actual' => $moneyActual,
                'sale_price' => $saleItem->price,
                'purchase_price' => $previousCost,
                'promedial_cost' => $previousCost,
            ]);

            Log::info("Kardex registrado - SALE_CANCELLATION", [
                'kardex_id' => $kardex->id,
                'sale_id' => $sale->id,
                'inventory_id' => $saleItem->inventory_id,
                'stock_in' => $stockIn,
                'stock_actual' => $stockActual
            ]);

            return $kardex;

        } catch (Exception $e) {
            Log::error("Error al registrar kardex SALE_CANCELLATION: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Registrar entrada de inventario por compra
     *
     * @param PurchaseItem $purchaseItem
     * @param PurchasesHeader $purchase
     * @return Kardex
     * @throws Exception
     */
    public function registerPurchaseMovement(PurchaseItem $purchaseItem, PurchasesHeader $purchase): Kardex
    {
        try {
            // Obtener el batch asociado al purchase_item
            $batch = Batch::where('purchase_item_id', $purchaseItem->id)->first();

            if (!$batch) {
                throw new Exception("No se encontró batch asociado al item de compra {$purchaseItem->id}");
            }

            $inventoryId = $batch->inventory_id;
            $warehouseId = $purchase->warehouse;

            // Obtener el último registro de kardex para este inventario
            $previousKardex = $this->getLastKardexEntry($inventoryId, $warehouseId);

            $previousStock = $previousKardex ? $previousKardex->stock_actual : 0;
            $previousMoney = $previousKardex ? $previousKardex->money_actual : 0;
            $previousCost = $previousKardex ? $previousKardex->promedial_cost : 0;

            // Calcular nuevos valores
            $stockIn = $purchaseItem->quantity;
            $stockActual = $previousStock + $stockIn;

            // Calcular dinero de entrada
            $moneyIn = $stockIn * $purchaseItem->price;
            $moneyActual = $previousMoney + $moneyIn;

            // Calcular COSTO PROMEDIO PONDERADO
            // Formula: (stock_anterior * costo_anterior + entrada * precio_compra) / stock_total
            if ($stockActual > 0) {
                $promedialCost = (($previousStock * $previousCost) + ($stockIn * $purchaseItem->price)) / $stockActual;
            } else {
                $promedialCost = $purchaseItem->price;
            }

            // Obtener el inventory_batch_id
            $inventoryBatchId = null;
            if ($batch) {
                $inventoryBatch = InventoriesBatch::where('id_batch', $batch->id)
                    ->where('id_inventory', $inventoryId)
                    ->first();
                $inventoryBatchId = $inventoryBatch?->id;
            }

            // Crear entrada en kardex
            $kardex = Kardex::create([
                'branch_id' => $warehouseId,
                'date' => now(),
                'operation_type' => 'PURCHASE',
                'operation_id' => $purchase->id,
                'operation_detail_id' => $purchaseItem->id,
                'document_type' => 'COMPRA',
                'document_number' => $purchase->purchase_number,
                'entity' => $purchase->provider?->comercial_name ?? 'PROVEEDOR',
                'nationality' => 'NACIONAL',
                'inventory_id' => $inventoryId,
                'inventory_batch_id' => $inventoryBatchId,
                'previous_stock' => $previousStock,
                'stock_in' => $stockIn,
                'stock_out' => 0,
                'stock_actual' => $stockActual,
                'money_in' => $moneyIn,
                'money_out' => 0,
                'money_actual' => $moneyActual,
                'sale_price' => 0,
                'purchase_price' => $purchaseItem->price,
                'promedial_cost' => $promedialCost,
            ]);

            Log::info("Kardex registrado - PURCHASE", [
                'kardex_id' => $kardex->id,
                'purchase_id' => $purchase->id,
                'inventory_id' => $inventoryId,
                'stock_in' => $stockIn,
                'stock_actual' => $stockActual,
                'promedial_cost' => $promedialCost
            ]);

            return $kardex;

        } catch (Exception $e) {
            Log::error("Error al registrar kardex PURCHASE: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Registrar salida de inventario por anulación de compra
     *
     * @param PurchaseItem $purchaseItem
     * @param PurchasesHeader $purchase
     * @return Kardex
     * @throws Exception
     */
    public function registerPurchaseCancellation(PurchaseItem $purchaseItem, PurchasesHeader $purchase): Kardex
    {
        try {
            // Obtener el batch asociado al purchase_item
            $batch = Batch::where('purchase_item_id', $purchaseItem->id)->first();

            if (!$batch) {
                throw new Exception("No se encontró batch asociado al item de compra {$purchaseItem->id}");
            }

            $inventoryId = $batch->inventory_id;
            $warehouseId = $purchase->warehouse;

            // Obtener el último registro de kardex para este inventario
            $previousKardex = $this->getLastKardexEntry($inventoryId, $warehouseId);

            $previousStock = $previousKardex ? $previousKardex->stock_actual : 0;
            $previousMoney = $previousKardex ? $previousKardex->money_actual : 0;
            $previousCost = $previousKardex ? $previousKardex->promedial_cost : 0;

            // Calcular nuevos valores (salida porque estamos anulando)
            $stockOut = $purchaseItem->quantity;
            $stockActual = $previousStock - $stockOut;

            // Calcular dinero de salida
            $moneyOut = $stockOut * $previousCost;
            $moneyActual = $previousMoney - $moneyOut;

            // Obtener el inventory_batch_id
            $inventoryBatchId = null;
            if ($batch) {
                $inventoryBatch = InventoriesBatch::where('id_batch', $batch->id)
                    ->where('id_inventory', $inventoryId)
                    ->first();
                $inventoryBatchId = $inventoryBatch?->id;
            }

            // Crear entrada en kardex
            $kardex = Kardex::create([
                'branch_id' => $warehouseId,
                'date' => now(),
                'operation_type' => 'PURCHASE_CANCELLATION',
                'operation_id' => $purchase->id,
                'operation_detail_id' => $purchaseItem->id,
                'document_type' => 'ANULACION COMPRA',
                'document_number' => $purchase->purchase_number,
                'entity' => $purchase->provider?->comercial_name ?? 'PROVEEDOR',
                'nationality' => 'NACIONAL',
                'inventory_id' => $inventoryId,
                'inventory_batch_id' => $inventoryBatchId,
                'previous_stock' => $previousStock,
                'stock_in' => 0,
                'stock_out' => $stockOut,
                'stock_actual' => $stockActual,
                'money_in' => 0,
                'money_out' => $moneyOut,
                'money_actual' => $moneyActual,
                'sale_price' => 0,
                'purchase_price' => $purchaseItem->price,
                'promedial_cost' => $previousCost, // Se mantiene el costo anterior
            ]);

            Log::info("Kardex registrado - PURCHASE_CANCELLATION", [
                'kardex_id' => $kardex->id,
                'purchase_id' => $purchase->id,
                'inventory_id' => $inventoryId,
                'stock_out' => $stockOut,
                'stock_actual' => $stockActual
            ]);

            return $kardex;

        } catch (Exception $e) {
            Log::error("Error al registrar kardex PURCHASE_CANCELLATION: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener la última entrada del kardex para un inventario en una sucursal
     *
     * @param int $inventoryId
     * @param int $branchId
     * @return Kardex|null
     */
    private function getLastKardexEntry(int $inventoryId, int $branchId): ?Kardex
    {
        return Kardex::where('inventory_id', $inventoryId)
            ->where('branch_id', $branchId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Obtener el stock actual de un inventario según kardex
     *
     * @param int $inventoryId
     * @param int $branchId
     * @return float
     */
    public function getCurrentStock(int $inventoryId, int $branchId): float
    {
        $lastEntry = $this->getLastKardexEntry($inventoryId, $branchId);
        return $lastEntry ? $lastEntry->stock_actual : 0;
    }

    /**
     * Obtener el costo promedio actual de un inventario
     *
     * @param int $inventoryId
     * @param int $branchId
     * @return float
     */
    public function getCurrentAverageCost(int $inventoryId, int $branchId): float
    {
        $lastEntry = $this->getLastKardexEntry($inventoryId, $branchId);
        return $lastEntry ? $lastEntry->promedial_cost : 0;
    }
}