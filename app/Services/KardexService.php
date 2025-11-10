<?php

namespace App\Services;

use App\Models\Kardex;
use App\Models\SaleItem;
use App\Models\SalesHeader;
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
            DB::beginTransaction();

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

            // Crear entrada en kardex
            $kardex = Kardex::create([
                'branch_id' => $sale->warehouse_id,
                'date' => $sale->sale_date ?? now(),
                'operation_type' => 'SALE',
                'operation_id' => $sale->id,
                'operation_detail_id' => $saleItem->id,
                'document_type' => $sale->documentType?->name ?? 'VENTA',
                'document_number' => $sale->document_internal_number,
                'entity' => $sale->customer?->name ?? 'CLIENTE GENERICO',
                'nationality' => $sale->customer?->nationality ?? 'NACIONAL',
                'inventory_id' => $saleItem->inventory_id,
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

            DB::commit();

            Log::info("Kardex registrado - SALE", [
                'kardex_id' => $kardex->id,
                'sale_id' => $sale->id,
                'inventory_id' => $saleItem->inventory_id,
                'stock_out' => $stockOut,
                'stock_actual' => $stockActual
            ]);

            return $kardex;

        } catch (Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

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

            DB::commit();

            Log::info("Kardex registrado - SALE_CANCELLATION", [
                'kardex_id' => $kardex->id,
                'sale_id' => $sale->id,
                'inventory_id' => $saleItem->inventory_id,
                'stock_in' => $stockIn,
                'stock_actual' => $stockActual
            ]);

            return $kardex;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar kardex SALE_CANCELLATION: " . $e->getMessage());
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