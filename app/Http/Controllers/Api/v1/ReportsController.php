<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SalesHeader;
use App\Models\SaleItem;
use App\Models\PurchasesHeader;
use App\Models\PurchaseItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\HistoryDte;
use App\Models\SalePaymentDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesReportExport;
use App\Exports\GenericReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * Helper method to parse date range including the full end day
     */
    private function parseDateRange(Request $request): array
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        // Asegurar que el rango incluya todo el día final
        $dateFromStart = Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = Carbon::parse($dateTo)->endOfDay();

        return [$dateFromStart, $dateToEnd];
    }

    // ========== REPORTES DE VENTAS ==========

    /**
     * Reporte de ventas por período
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $query = SalesHeader::with(['customer', 'seller', 'warehouse', 'saleDetails'])
                ->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                ->where('sale_status', '2');

            // Filtros adicionales
            if ($request->has('seller_id')) {
                $query->where('seller_id', $request->input('seller_id'));
            }

            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->input('customer_id'));
            }

            if ($request->has('status')) {
                $query->where('sale_status', $request->input('status'));
            }

            $sales = $query->orderBy('sale_date', 'desc')->get();

            // Calcular resumen
            $summary = [
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('sale_total'),
                'average_ticket' => $sales->count() > 0 ? $sales->sum('sale_total') / $sales->count() : 0,
                'total_items' => $sales->sum(function ($sale) {
                    return $sale->saleDetails->sum('quantity');
                }),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $sales
            ], 'Reporte de ventas generado', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de ventas por vendedor
     */
    public function getSalesBySeller(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $salesBySeller = SalesHeader::select(
                    'seller_id',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(sale_total) as total_amount'),
                    DB::raw('AVG(sale_total) as average_ticket')
                )
                ->with('seller')
                ->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                ->where('sale_status', '2')
                ->groupBy('seller_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $summary = [
                'total_sellers' => $salesBySeller->count(),
                'total_amount' => $salesBySeller->sum('total_amount'),
                'average_per_seller' => $salesBySeller->count() > 0 ?
                    $salesBySeller->sum('total_amount') / $salesBySeller->count() : 0,
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $salesBySeller
            ], 'Reporte de ventas por vendedor', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de ventas por cliente
     */
    public function getSalesByCustomer(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $salesByCustomer = SalesHeader::select(
                    'customer_id',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(sale_total) as total_amount'),
                    DB::raw('AVG(sale_total) as average_ticket'),
                    DB::raw('MAX(sale_date) as last_sale_date')
                )
                ->with('customer')
                ->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                ->where('sale_status', '2')
                ->groupBy('customer_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $summary = [
                'total_customers' => $salesByCustomer->count(),
                'total_amount' => $salesByCustomer->sum('total_amount'),
                'average_per_customer' => $salesByCustomer->count() > 0 ?
                    $salesByCustomer->sum('total_amount') / $salesByCustomer->count() : 0,
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $salesByCustomer
            ], 'Reporte de ventas por cliente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de ventas por producto
     */
    public function getSalesByProduct(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $salesByProduct = SaleItem::select(
                    'inventories.product_id',
                    DB::raw('SUM(sale_items.quantity) as total_quantity'),
                    DB::raw('SUM(sale_items.total) as total_amount'),
                    DB::raw('AVG(sale_items.price) as average_price'),
                    DB::raw('COUNT(DISTINCT sale_items.sale_id) as sales_count')
                )
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->whereHas('sale', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                          ->where('sale_status', '2');
                })
                ->groupBy('inventories.product_id')
                ->orderBy('total_amount', 'desc')
                ->get()
                ->map(function($item) {
                    $product = \App\Models\Product::find($item->product_id);
                    return [
                        'product_id' => $item->product_id,
                        'product_code' => $product->code ?? 'N/A',
                        'product_name' => $product->name ?? 'N/A',
                        'total_quantity' => $item->total_quantity,
                        'total_amount' => $item->total_amount,
                        'average_price' => round($item->average_price, 2),
                        'sales_count' => $item->sales_count,
                    ];
                });

            $summary = [
                'total_products' => $salesByProduct->count(),
                'total_quantity' => $salesByProduct->sum('total_quantity'),
                'total_amount' => $salesByProduct->sum('total_amount'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $salesByProduct
            ], 'Reporte de ventas por producto', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de ventas por método de pago
     */
    public function getSalesByPaymentMethod(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $salesByPayment = SalePaymentDetail::select(
                    'payment_method_id',
                    DB::raw('COUNT(DISTINCT sale_id) as total_sales'),
                    DB::raw('SUM(payment_amount) as total_amount'),
                    DB::raw('AVG(payment_amount) as average_amount')
                )
                ->with('paymentMethod')
                ->whereHas('sale', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                          ->where('sale_status', '2');
                })
                ->groupBy('payment_method_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $summary = [
                'total_methods' => $salesByPayment->count(),
                'total_amount' => $salesByPayment->sum('total_amount'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $salesByPayment
            ], 'Reporte de ventas por método de pago', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    // ========== REPORTES DE COMPRAS ==========

    /**
     * Reporte de compras por período
     */
    public function getPurchasesReport(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $query = PurchasesHeader::with(['provider', 'warehouse'])
                ->whereBetween('purchase_date', [$dateFromStart, $dateToEnd]);

            if ($request->has('provider_id')) {
                $query->where('provider_id', $request->input('provider_id'));
            }

            $purchases = $query->orderBy('purchase_date', 'desc')->get();

            $summary = [
                'total_purchases' => $purchases->count(),
                'total_amount' => $purchases->sum('total_purchase'),
                'average_purchase' => $purchases->count() > 0 ? $purchases->sum('total_purchase') / $purchases->count() : 0,
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $purchases
            ], 'Reporte de compras generado', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de compras por proveedor
     */
    public function getPurchasesByProvider(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $purchasesByProvider = PurchasesHeader::select(
                    'provider_id',
                    DB::raw('COUNT(*) as total_purchases'),
                    DB::raw('SUM(total_purchase) as total_amount'),
                    DB::raw('AVG(total_purchase) as average_purchase'),
                    DB::raw('MAX(purchase_date) as last_purchase_date')
                )
                ->with('provider')
                ->whereBetween('purchase_date', [$dateFromStart, $dateToEnd])
                ->groupBy('provider_id')
                ->orderBy('total_amount', 'desc')
                ->get();

            $summary = [
                'total_providers' => $purchasesByProvider->count(),
                'total_amount' => $purchasesByProvider->sum('total_amount'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $purchasesByProvider
            ], 'Reporte de compras por proveedor', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de compras por producto
     */
    public function getPurchasesByProduct(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $purchasesByProduct = PurchaseItem::select(
                    'inventories.product_id',
                    DB::raw('SUM(purchase_items.quantity) as total_quantity'),
                    DB::raw('SUM(purchase_items.total) as total_amount'),
                    DB::raw('AVG(purchase_items.price) as average_price'),
                    DB::raw('COUNT(DISTINCT purchase_items.purchase_id) as purchases_count')
                )
                ->join('batches', 'purchase_items.id', '=', 'batches.purchase_item_id')
                ->join('inventories', 'batches.inventory_id', '=', 'inventories.id')
                ->whereHas('purchase', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('purchase_date', [$dateFromStart, $dateToEnd]);
                })
                ->groupBy('inventories.product_id')
                ->orderBy('total_amount', 'desc')
                ->get()
                ->map(function($item) {
                    $product = \App\Models\Product::find($item->product_id);
                    return [
                        'product_id' => $item->product_id,
                        'product_code' => $product->code ?? 'N/A',
                        'product_name' => $product->name ?? 'N/A',
                        'total_quantity' => $item->total_quantity,
                        'total_amount' => $item->total_amount,
                        'average_price' => round($item->average_price, 2),
                        'purchases_count' => $item->purchases_count,
                    ];
                });

            $summary = [
                'total_products' => $purchasesByProduct->count(),
                'total_quantity' => $purchasesByProduct->sum('total_quantity'),
                'total_amount' => $purchasesByProduct->sum('total_amount'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $purchasesByProduct
            ], 'Reporte de compras por producto', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    // ========== REPORTES DE INVENTARIO ==========

    /**
     * Reporte de valorización de inventario
     */
    public function getInventoryValuation(Request $request): JsonResponse
    {
        try {
            $query = Inventory::with(['product', 'warehouse', 'inventoryBatches.batch.purchaseItem'])
                ->where('stock_actual_quantity', '>', 0);

            if ($request->has('warehouse_id')) {
                $query->where('warehouse_id', $request->input('warehouse_id'));
            }

            $inventories = $query->get()->filter(function ($inventory) {
                return $inventory->product !== null && $inventory->warehouse !== null;
            })->map(function ($inventory) {
                $product = $inventory->product;

                // Calcular costo promedio ponderado basado en lotes disponibles
                $inventoryBatches = $inventory->inventoryBatches()
                    ->with('batch.purchaseItem')
                    ->get();

                $totalQuantityInBatches = 0;
                $totalCostInBatches = 0;
                $batchesCount = 0;

                foreach ($inventoryBatches as $inventoryBatch) {
                    $batch = $inventoryBatch->batch;

                    if ($batch && $batch->is_active && $batch->available_quantity > 0) {
                        $batchPrice = $batch->purchaseItem->price ?? 0;
                        $batchQuantity = $batch->available_quantity; // Usar available_quantity del batch

                        $totalQuantityInBatches += $batchQuantity;
                        $totalCostInBatches += ($batchQuantity * $batchPrice);
                        $batchesCount++;
                    }
                }

                // Costo promedio ponderado
                $avgCostPrice = $totalQuantityInBatches > 0
                    ? ($totalCostInBatches / $totalQuantityInBatches)
                    : ($product->cost_price ?? 0);

                $salePrice = $product->sale_price ?? 0;
                $quantity = $inventory->stock_actual_quantity; // Usar stock_actual_quantity del inventario
                $totalCost = $quantity * $avgCostPrice;
                $totalSale = $quantity * $salePrice;
                $potentialProfit = $totalSale - $totalCost;

                return [
                    'product_code' => $product->code ?? 'N/A',
                    'product_name' => $product->name ?? 'N/A',
                    'warehouse' => $inventory->warehouse->name ?? 'N/A',
                    'quantity' => $quantity,
                    'batches_count' => $batchesCount,
                    'avg_cost_price' => round($avgCostPrice, 2),
                    'sale_price' => $salePrice,
                    'total_cost' => round($totalCost, 2),
                    'total_sale_value' => round($totalSale, 2),
                    'potential_profit' => round($potentialProfit, 2),
                ];
            })->values();

            $summary = [
                'total_products' => $inventories->count(),
                'total_quantity' => $inventories->sum('quantity'),
                'total_inventory_value' => $inventories->sum('total_cost'),
                'total_sale_value' => $inventories->sum('total_sale_value'),
                'potential_profit' => $inventories->sum('potential_profit'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $inventories
            ], 'Reporte de valorización de inventario', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de rotación de inventario
     */
    public function getInventoryRotation(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);
            $days = $dateFromStart->diffInDays($dateToEnd);

            // Obtener ventas por producto en el período
            $salesByProduct = SaleItem::select(
                    'inventories.product_id',
                    DB::raw('SUM(sale_items.quantity) as total_sold')
                )
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->whereHas('sale', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                          ->where('sale_status', '2');
                })
                ->groupBy('inventories.product_id')
                ->pluck('total_sold', 'product_id');

            // Obtener inventario actual
            $inventories = Inventory::with(['product'])
                ->where('stock_actual_quantity', '>', 0)
                ->get()
                ->filter(function ($inventory) {
                    return $inventory->product !== null;
                })
                ->map(function ($inventory) use ($salesByProduct, $days) {
                    $productId = $inventory->product_id;
                    $sold = $salesByProduct[$productId] ?? 0;
                    $avgStock = $inventory->stock_actual_quantity;
                    $rotation = $avgStock > 0 ? ($sold / $avgStock) : 0;
                    $daysToSellout = $sold > 0 ? ($avgStock / $sold) * $days : 999;

                    return [
                        'product_code' => $inventory->product->code ?? 'N/A',
                        'product_name' => $inventory->product->name ?? 'N/A',
                        'current_stock' => $inventory->stock_actual_quantity,
                        'total_sold' => $sold,
                        'rotation_index' => round($rotation, 2),
                        'days_to_sellout' => round($daysToSellout, 0),
                        'rotation_status' => $rotation > 2 ? 'Alta' : ($rotation > 0.5 ? 'Media' : 'Baja')
                    ];
                })
                ->sortByDesc('rotation_index')
                ->values();

            $summary = [
                'total_products' => $inventories->count(),
                'average_rotation' => $inventories->avg('rotation_index'),
                'high_rotation' => $inventories->where('rotation_status', 'Alta')->count(),
                'medium_rotation' => $inventories->where('rotation_status', 'Media')->count(),
                'low_rotation' => $inventories->where('rotation_status', 'Baja')->count(),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $inventories
            ], 'Reporte de rotación de inventario', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de productos sin movimiento
     */
    public function getProductsWithoutMovement(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 90);
            $dateLimit = Carbon::now()->subDays($days);

            // Obtener productos con stock
            $inventories = Inventory::with(['product', 'inventoryBatches.batch.purchaseItem'])
                ->where('stock_actual_quantity', '>', 0)
                ->get()
                ->filter(function ($inventory) {
                    return $inventory->product !== null;
                });

            $productsWithoutMovement = [];

            foreach ($inventories as $inventory) {
                // Buscar última venta del producto
                $lastSale = SaleItem::join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                    ->where('inventories.product_id', $inventory->product_id)
                    ->whereHas('sale', function($query) {
                        $query->where('sale_status', '2');
                    })
                    ->orderBy('sale_items.created_at', 'desc')
                    ->select('sale_items.*')
                    ->first();

                // Si no hay venta o la última venta es anterior a la fecha límite
                if (!$lastSale || $lastSale->created_at < $dateLimit) {
                    $daysSinceLastSale = $lastSale ?
                        Carbon::parse($lastSale->created_at)->diffInDays(Carbon::now()) : 999;

                    // Calcular costo promedio ponderado basado en lotes disponibles
                    $inventoryBatches = $inventory->inventoryBatches;
                    $totalQuantityInBatches = 0;
                    $totalCostInBatches = 0;

                    foreach ($inventoryBatches as $inventoryBatch) {
                        $batch = $inventoryBatch->batch;
                        if ($batch && $batch->is_active && $batch->available_quantity > 0) {
                            $batchPrice = $batch->purchaseItem->price ?? 0;
                            $batchQuantity = $batch->available_quantity;

                            $totalQuantityInBatches += $batchQuantity;
                            $totalCostInBatches += ($batchQuantity * $batchPrice);
                        }
                    }

                    // Costo promedio ponderado
                    $avgCostPrice = $totalQuantityInBatches > 0
                        ? ($totalCostInBatches / $totalQuantityInBatches)
                        : ($inventory->product->cost_price ?? 0);

                    $productsWithoutMovement[] = [
                        'product_code' => $inventory->product->code ?? 'N/A',
                        'product_name' => $inventory->product->name ?? 'N/A',
                        'current_stock' => $inventory->stock_actual_quantity,
                        'cost_price' => round($avgCostPrice, 2),
                        'inventory_value' => round($inventory->stock_actual_quantity * $avgCostPrice, 2),
                        'last_sale_date' => $lastSale ? $lastSale->created_at->format('Y-m-d') : 'Nunca',
                        'days_without_movement' => $daysSinceLastSale,
                    ];
                }
            }

            $productsWithoutMovement = collect($productsWithoutMovement)
                ->sortByDesc('days_without_movement')
                ->values();

            $summary = [
                'total_products' => count($productsWithoutMovement),
                'total_inventory_value' => collect($productsWithoutMovement)->sum('inventory_value'),
                'days_threshold' => $days,
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $productsWithoutMovement
            ], 'Reporte de productos sin movimiento', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de stock por almacén
     */
    public function getStockByWarehouse(Request $request): JsonResponse
    {
        try {
            $stockByWarehouse = Inventory::select(
                    'warehouse_id',
                    DB::raw('COUNT(DISTINCT product_id) as total_products'),
                    DB::raw('SUM(stock_actual_quantity) as total_quantity')
                )
                ->with('warehouse')
                ->where('stock_actual_quantity', '>', 0)
                ->groupBy('warehouse_id')
                ->get()
                ->filter(function ($item) {
                    return $item->warehouse !== null;
                })
                ->map(function ($item) {
                    return [
                        'warehouse_name' => $item->warehouse->name ?? 'N/A',
                        'total_products' => $item->total_products,
                        'total_quantity' => $item->total_quantity,
                    ];
                })
                ->values();

            $summary = [
                'total_warehouses' => $stockByWarehouse->count(),
                'total_products' => $stockByWarehouse->sum('total_products'),
                'total_quantity' => $stockByWarehouse->sum('total_quantity'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $stockByWarehouse
            ], 'Reporte de stock por almacén', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de productos bajo stock mínimo
     */
    public function getLowStockReport(Request $request): JsonResponse
    {
        try {
            $lowStockProducts = Inventory::with(['product', 'warehouse'])
                ->whereRaw('stock_actual_quantity < stock_min')
                ->orWhere('stock_actual_quantity', '=', 0)
                ->get()
                ->filter(function ($inventory) {
                    return $inventory->product !== null && $inventory->warehouse !== null;
                })
                ->map(function ($inventory) {
                    $deficit = $inventory->stock_min - $inventory->stock_actual_quantity;

                    return [
                        'product_code' => $inventory->product->code ?? 'N/A',
                        'product_name' => $inventory->product->name ?? 'N/A',
                        'warehouse' => $inventory->warehouse->name ?? 'N/A',
                        'current_stock' => $inventory->stock_actual_quantity,
                        'minimum_stock' => $inventory->stock_min,
                        'deficit' => $deficit > 0 ? $deficit : 0,
                        'status' => $inventory->stock_actual_quantity == 0 ? 'Sin Stock' : 'Bajo Mínimo',
                    ];
                })
                ->values();

            $summary = [
                'total_products' => $lowStockProducts->count(),
                'out_of_stock' => $lowStockProducts->where('status', 'Sin Stock')->count(),
                'below_minimum' => $lowStockProducts->where('status', 'Bajo Mínimo')->count(),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $lowStockProducts
            ], 'Reporte de productos bajo stock', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    // ========== REPORTES DE RENTABILIDAD ==========

    /**
     * Reporte de rentabilidad por producto
     */
    public function getProfitabilityByProduct(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            // Obtener ventas por producto
            $salesByProduct = SaleItem::select(
                    'inventories.product_id',
                    DB::raw('SUM(sale_items.quantity) as total_sold'),
                    DB::raw('SUM(sale_items.total) as total_revenue')
                )
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->whereHas('sale', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                          ->where('sale_status', '2');
                })
                ->groupBy('inventories.product_id')
                ->get();

            $profitability = collect([]);

            foreach ($salesByProduct as $sale) {
                // Obtener inventario con lotes para calcular costo promedio
                $inventory = Inventory::with('inventoryBatches.batch.purchaseItem')
                    ->where('product_id', $sale->product_id)
                    ->first();

                if (!$inventory) continue;

                // Calcular costo promedio ponderado desde lotes
                $totalQuantityInBatches = 0;
                $totalCostInBatches = 0;

                foreach ($inventory->inventoryBatches as $inventoryBatch) {
                    $batch = $inventoryBatch->batch;
                    if ($batch && $batch->is_active && $batch->available_quantity > 0) {
                        $batchPrice = $batch->purchaseItem->price ?? 0;
                        $batchQuantity = $batch->available_quantity;
                        $totalQuantityInBatches += $batchQuantity;
                        $totalCostInBatches += ($batchQuantity * $batchPrice);
                    }
                }

                $avgCostPrice = $totalQuantityInBatches > 0
                    ? ($totalCostInBatches / $totalQuantityInBatches)
                    : 0;

                $totalCost = $sale->total_sold * $avgCostPrice;
                $totalProfit = $sale->total_revenue - $totalCost;
                $marginPercent = $sale->total_revenue > 0 ?
                    (($totalProfit / $sale->total_revenue) * 100) : 0;

                $product = $inventory->product;
                if (!$product) continue;

                $profitability->push([
                    'product_code' => $product->code ?? 'N/A',
                    'product_name' => $product->name ?? 'N/A',
                    'total_sold' => $sale->total_sold,
                    'total_revenue' => round($sale->total_revenue, 2),
                    'avg_cost_price' => round($avgCostPrice, 2),
                    'total_cost' => round($totalCost, 2),
                    'total_profit' => round($totalProfit, 2),
                    'profit_margin_percent' => round($marginPercent, 2),
                ]);
            }

            $profitability = $profitability->sortByDesc('total_profit')->values();

            $summary = [
                'total_products' => $profitability->count(),
                'total_revenue' => $profitability->sum('total_revenue'),
                'total_cost' => $profitability->sum('total_cost'),
                'total_profit' => $profitability->sum('total_profit'),
                'average_margin' => $profitability->avg('profit_margin_percent'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $profitability
            ], 'Reporte de rentabilidad por producto', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de rentabilidad por categoría
     */
    public function getProfitabilityByCategory(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            // Obtener ventas agrupadas por categoría y producto
            $salesByProduct = SaleItem::select(
                    'products.category_id',
                    'inventories.product_id',
                    DB::raw('SUM(sale_items.quantity) as total_sold'),
                    DB::raw('SUM(sale_items.total) as total_revenue')
                )
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->whereHas('sale', function($query) use ($dateFromStart, $dateToEnd) {
                    $query->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                          ->where('sale_status', '2');
                })
                ->groupBy('products.category_id', 'inventories.product_id')
                ->get();

            // Agrupar por categoría y calcular costos desde lotes
            $categoriesData = [];

            foreach ($salesByProduct as $sale) {
                $categoryId = $sale->category_id;

                if (!isset($categoriesData[$categoryId])) {
                    $categoriesData[$categoryId] = [
                        'total_sold' => 0,
                        'total_revenue' => 0,
                        'total_cost' => 0,
                    ];
                }

                // Obtener costo promedio desde lotes
                $inventory = Inventory::with('inventoryBatches.batch.purchaseItem')
                    ->where('product_id', $sale->product_id)
                    ->first();

                if ($inventory) {
                    $totalQuantityInBatches = 0;
                    $totalCostInBatches = 0;

                    foreach ($inventory->inventoryBatches as $inventoryBatch) {
                        $batch = $inventoryBatch->batch;
                        if ($batch && $batch->is_active && $batch->available_quantity > 0) {
                            $batchPrice = $batch->purchaseItem->price ?? 0;
                            $batchQuantity = $batch->available_quantity;
                            $totalQuantityInBatches += $batchQuantity;
                            $totalCostInBatches += ($batchQuantity * $batchPrice);
                        }
                    }

                    $avgCostPrice = $totalQuantityInBatches > 0
                        ? ($totalCostInBatches / $totalQuantityInBatches)
                        : 0;

                    $categoriesData[$categoryId]['total_sold'] += $sale->total_sold;
                    $categoriesData[$categoryId]['total_revenue'] += $sale->total_revenue;
                    $categoriesData[$categoryId]['total_cost'] += ($sale->total_sold * $avgCostPrice);
                }
            }

            // Formatear resultados
            $profitabilityByCategory = collect([]);

            foreach ($categoriesData as $categoryId => $data) {
                $category = \App\Models\Category::find($categoryId);
                if (!$category) continue;

                $totalProfit = $data['total_revenue'] - $data['total_cost'];
                $marginPercent = $data['total_revenue'] > 0 ?
                    (($totalProfit / $data['total_revenue']) * 100) : 0;

                $profitabilityByCategory->push([
                    'category_name' => $category->name ?? 'N/A',
                    'total_sold' => $data['total_sold'],
                    'total_revenue' => round($data['total_revenue'], 2),
                    'total_cost' => round($data['total_cost'], 2),
                    'total_profit' => round($totalProfit, 2),
                    'profit_margin_percent' => round($marginPercent, 2),
                ]);
            }

            $profitabilityByCategory = $profitabilityByCategory->sortByDesc('total_profit')->values();

            $summary = [
                'total_categories' => $profitabilityByCategory->count(),
                'total_revenue' => $profitabilityByCategory->sum('total_revenue'),
                'total_cost' => $profitabilityByCategory->sum('total_cost'),
                'total_profit' => $profitabilityByCategory->sum('total_profit'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $profitabilityByCategory
            ], 'Reporte de rentabilidad por categoría', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de margen de ganancia
     */
    public function getProfitMargin(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            // Cargar relaciones necesarias para obtener el costo desde los lotes
            // SaleItem → batch (Batch) → purchaseItem (PurchaseItem)
            $sales = SalesHeader::with(['saleDetails.batch.purchaseItem', 'customer'])
                ->whereBetween('sale_date', [$dateFromStart, $dateToEnd])
                ->where('sale_status', '2')
                ->get();

            $data = $sales->map(function ($sale) {
                $totalCost = $sale->saleDetails->sum(function ($item) {
                    // Obtener el costo desde el lote (batch → purchaseItem → price)
                    $costPrice = 0;
                    if ($item->batch && $item->batch->purchaseItem) {
                        $costPrice = $item->batch->purchaseItem->price ?? 0;
                    }
                    return $item->quantity * $costPrice;
                });

                $totalRevenue = $sale->sale_total;
                $profit = $totalRevenue - $totalCost;
                $marginPercent = $totalRevenue > 0 ? (($profit / $totalRevenue) * 100) : 0;

                return [
                    'sale_id' => $sale->id,
                    'date' => $sale->sale_date,
                    'customer' => $sale->customer->name ?? 'N/A',
                    'total_revenue' => round($totalRevenue, 2),
                    'total_cost' => round($totalCost, 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => round($marginPercent, 2),
                ];
            });

            $summary = [
                'total_sales' => $data->count(),
                'total_revenue' => round($data->sum('total_revenue'), 2),
                'total_cost' => round($data->sum('total_cost'), 2),
                'total_profit' => round($data->sum('profit'), 2),
                'average_margin' => round($data->avg('margin_percent') ?? 0, 2),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $data
            ], 'Reporte de margen de ganancia', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    // ========== REPORTES DE DTEs ==========

    /**
     * Reporte de DTEs emitidos
     */
    public function getDTEsReport(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $dtes = HistoryDte::with('salesHeader.customer')
                ->whereBetween('created_at', [$dateFromStart, $dateToEnd])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($dte) {
                    return [
                        'id' => $dte->id,
                        'codigoGeneracion' => $dte->codigoGeneracion,
                        'num_control' => $dte->num_control,
                        'document_type' => $dte->document_type,
                        'document_number' => $dte->document_number,
                        'version' => $dte->version,
                        'ambiente' => $dte->ambiente,
                        'estado' => $dte->estado,
                        'fhProcesamiento' => $dte->fhProcesamiento,
                        'fecha_emision' => $dte->created_at,
                        'descripcionMsg' => $dte->descripcionMsg,
                        'observaciones' => $dte->observaciones,
                        'customer' => $dte->salesHeader && $dte->salesHeader->customer
                            ? $dte->salesHeader->customer->name
                            : 'N/A',
                        'sale_id' => $dte->salesHeader ? $dte->salesHeader->id : null,
                        'sale_total' => $dte->salesHeader ? $dte->salesHeader->sale_total : 0,
                    ];
                });

            $summary = [
                'total_dtes' => $dtes->count(),
                'procesado' => $dtes->where('estado', 'PROCESADO')->count(),
                'rechazado' => $dtes->where('estado', 'RECHAZADO')->count(),
                'observado' => $dtes->where('estado', 'OBSERVADO')->count(),
                'total_amount' => round($dtes->whereIn('estado', ['PROCESADO', 'OBSERVADO'])->sum('sale_total'), 2),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $dtes
            ], 'Reporte de DTEs emitidos', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de DTEs por estado
     */
    public function getDTEsByStatus(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $dtesByStatus = HistoryDte::select(
                    'estado',
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('created_at', [$dateFromStart, $dateToEnd])
                ->groupBy('estado')
                ->get()
                ->map(function ($item) {
                    // Obtener el total de ventas para este estado
                    $dtes = HistoryDte::with('salesHeader')
                        ->where('estado', $item->estado)
                        ->get();

                    $totalAmount = $dtes->sum(function ($dte) {
                        return $dte->salesHeader ? $dte->salesHeader->sale_total : 0;
                    });

                    return [
                        'estado' => $item->estado,
                        'total' => $item->total,
                        'total_amount' => round($totalAmount, 2),
                    ];
                });

            return ApiResponse::success([
                'data' => $dtesByStatus
            ], 'Reporte de DTEs por estado', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    /**
     * Reporte de DTEs rechazados
     */
    public function getRejectedDTEs(Request $request): JsonResponse
    {
        try {
            [$dateFromStart, $dateToEnd] = $this->parseDateRange($request);

            $rejectedDTEs = HistoryDte::with('salesHeader.customer')
                ->whereBetween('created_at', [$dateFromStart, $dateToEnd])
                ->where('estado', 'RECHAZADO')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($dte) {
                    return [
                        'id' => $dte->id,
                        'codigoGeneracion' => $dte->codigoGeneracion,
                        'num_control' => $dte->num_control,
                        'document_type' => $dte->document_type,
                        'document_number' => $dte->document_number,
                        'version' => $dte->version,
                        'ambiente' => $dte->ambiente,
                        'estado' => $dte->estado,
                        'fhProcesamiento' => $dte->fhProcesamiento,
                        'fecha_emision' => $dte->created_at,
                        'codigoMsg' => $dte->codigoMsg,
                        'descripcionMsg' => $dte->descripcionMsg,
                        'observaciones' => $dte->observaciones,
                        'customer' => $dte->salesHeader && $dte->salesHeader->customer
                            ? $dte->salesHeader->customer->name
                            : 'N/A',
                        'sale_id' => $dte->salesHeader ? $dte->salesHeader->id : null,
                        'sale_total' => $dte->salesHeader ? $dte->salesHeader->sale_total : 0,
                    ];
                });

            $summary = [
                'total_rejected' => $rejectedDTEs->count(),
                'total_amount' => round($rejectedDTEs->sum('sale_total'), 2),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $rejectedDTEs
            ], 'Reporte de DTEs rechazados', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }

    // ========== EXPORTACIÓN DE REPORTES ==========

    /**
     * Exportar reporte a Excel
     */
    public function exportToExcel(Request $request, string $reportType)
    {
        try {
            // Obtener los datos del reporte según el tipo
            $data = $this->getReportData($request, $reportType);

            // Nombre del archivo
            $filename = $reportType . '_' . now()->format('Y-m-d_His') . '.xlsx';

            // Exportar según el tipo de reporte
            if (in_array($reportType, ['sales', 'sales-by-seller', 'sales-by-customer', 'sales-by-product'])) {
                return Excel::download(
                    new SalesReportExport($data['data'], $reportType),
                    $filename
                );
            }

            // Para otros reportes, usar exportación genérica
            $headings = $this->getReportHeadings($reportType);
            $title = $this->getReportTitle($reportType);

            return Excel::download(
                new GenericReportExport($data['data'], $headings, $title),
                $filename
            );

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al exportar el reporte', 500);
        }
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportToPDF(Request $request, string $reportType)
    {
        try {
            // Obtener los datos del reporte
            $data = $this->getReportData($request, $reportType);

            // Preparar datos para la vista
            $viewData = [
                'title' => $this->getReportTitle($reportType),
                'summary' => $data['summary'] ?? [],
                'data' => $data['data'] ?? [],
                'headings' => $this->getReportHeadings($reportType),
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'filters' => $this->getFiltersText($request)
            ];

            // Generar PDF
            $pdf = Pdf::loadView('reports.generic-report', $viewData);
            $pdf->setPaper('A4', 'landscape');

            // Nombre del archivo
            $filename = $reportType . '_' . now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al exportar el reporte a PDF', 500);
        }
    }

    /**
     * Imprimir reporte (devuelve PDF para abrir en nueva ventana)
     */
    public function printReport(Request $request, string $reportType)
    {
        try {
            // Obtener los datos del reporte
            $data = $this->getReportData($request, $reportType);

            // Preparar datos para la vista
            $viewData = [
                'title' => $this->getReportTitle($reportType),
                'summary' => $data['summary'] ?? [],
                'data' => $data['data'] ?? [],
                'headings' => $this->getReportHeadings($reportType),
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'filters' => $this->getFiltersText($request)
            ];

            // Generar PDF
            $pdf = Pdf::loadView('reports.generic-report', $viewData);
            $pdf->setPaper('A4', 'landscape');

            return $pdf->stream();

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al imprimir el reporte', 500);
        }
    }

    /**
     * Obtener datos del reporte según el tipo
     */
    private function getReportData(Request $request, string $reportType): array
    {
        switch ($reportType) {
            case 'sales':
                $response = $this->getSalesReport($request);
                break;
            case 'sales-by-seller':
                $response = $this->getSalesBySeller($request);
                break;
            case 'sales-by-customer':
                $response = $this->getSalesByCustomer($request);
                break;
            case 'sales-by-product':
                $response = $this->getSalesByProduct($request);
                break;
            case 'sales-by-payment-method':
                $response = $this->getSalesByPaymentMethod($request);
                break;
            case 'purchases':
                $response = $this->getPurchasesReport($request);
                break;
            case 'purchases-by-provider':
                $response = $this->getPurchasesByProvider($request);
                break;
            case 'purchases-by-product':
                $response = $this->getPurchasesByProduct($request);
                break;
            case 'inventory-valuation':
                $response = $this->getInventoryValuation($request);
                break;
            case 'inventory-rotation':
                $response = $this->getInventoryRotation($request);
                break;
            case 'products-without-movement':
                $response = $this->getProductsWithoutMovement($request);
                break;
            case 'stock-by-warehouse':
                $response = $this->getStockByWarehouse($request);
                break;
            case 'low-stock':
                $response = $this->getLowStockReport($request);
                break;
            case 'profitability-by-product':
                $response = $this->getProfitabilityByProduct($request);
                break;
            case 'profitability-by-category':
                $response = $this->getProfitabilityByCategory($request);
                break;
            case 'profit-margin':
                $response = $this->getProfitMargin($request);
                break;
            case 'dtes':
                $response = $this->getDTEsReport($request);
                break;
            case 'dtes-by-status':
                $response = $this->getDTEsByStatus($request);
                break;
            case 'dtes-rejected':
                $response = $this->getRejectedDTEs($request);
                break;
            default:
                throw new \Exception('Tipo de reporte no válido');
        }

        return json_decode($response->getContent(), true)['data'];
    }

    /**
     * Obtener encabezados según el tipo de reporte
     */
    private function getReportHeadings(string $reportType): array
    {
        $headings = [
            'sales' => ['ID', 'Fecha', 'Cliente', 'Vendedor', 'Subtotal', 'Descuento', 'IVA', 'Total', 'Estado'],
            'sales-by-seller' => ['Vendedor', 'Total Ventas', 'Monto Total', 'Ticket Promedio'],
            'sales-by-customer' => ['Cliente', 'Total Ventas', 'Monto Total', 'Ticket Promedio', 'Última Venta'],
            'sales-by-product' => ['Código', 'Producto', 'Cantidad', 'Monto Total', 'Precio Prom', 'Núm Ventas'],
            'purchases' => ['ID', 'Fecha', 'Proveedor', 'Subtotal', 'IVA', 'Total'],
            'purchases-by-provider' => ['Proveedor', 'Total Compras', 'Monto Total', 'Compra Promedio', 'Última Compra'],
            'purchases-by-product' => ['Código', 'Producto', 'Cantidad', 'Monto Total', 'Precio Prom', 'Núm Compras'],
            'inventory-valuation' => ['Código', 'Producto', 'Almacén', 'Cantidad', 'Costo Unit', 'Precio Venta', 'Valor Costo', 'Valor Venta', 'Ganancia Potencial'],
            'low-stock' => ['Código', 'Producto', 'Almacén', 'Stock Actual', 'Stock Mínimo', 'Déficit', 'Estado'],
            'profitability-by-product' => ['Código', 'Producto', 'Cantidad', 'Ingresos', 'Costos', 'Ganancia', 'Margen %'],
            'dtes' => ['ID', 'Fecha Emisión', 'Cliente', 'Tipo DTE', 'Total', 'Estado'],
        ];

        return $headings[$reportType] ?? ['Datos'];
    }

    /**
     * Obtener título del reporte
     */
    private function getReportTitle(string $reportType): string
    {
        $titles = [
            'sales' => 'Reporte de Ventas',
            'sales-by-seller' => 'Ventas por Vendedor',
            'sales-by-customer' => 'Ventas por Cliente',
            'sales-by-product' => 'Ventas por Producto',
            'sales-by-payment-method' => 'Ventas por Método de Pago',
            'purchases' => 'Reporte de Compras',
            'purchases-by-provider' => 'Compras por Proveedor',
            'purchases-by-product' => 'Compras por Producto',
            'inventory-valuation' => 'Valorización de Inventario',
            'inventory-rotation' => 'Rotación de Inventario',
            'products-without-movement' => 'Productos sin Movimiento',
            'stock-by-warehouse' => 'Stock por Almacén',
            'low-stock' => 'Productos Bajo Stock',
            'profitability-by-product' => 'Rentabilidad por Producto',
            'profitability-by-category' => 'Rentabilidad por Categoría',
            'profit-margin' => 'Margen de Ganancia',
            'dtes' => 'DTEs Emitidos',
            'dtes-by-status' => 'DTEs por Estado',
            'dtes-rejected' => 'DTEs Rechazados',
        ];

        return $titles[$reportType] ?? 'Reporte';
    }

    /**
     * Obtener texto de filtros aplicados
     */
    private function getFiltersText(Request $request): string
    {
        $filters = [];

        if ($request->has('date_from')) {
            $filters[] = 'Desde: ' . Carbon::parse($request->date_from)->format('d/m/Y');
        }
        if ($request->has('date_to')) {
            $filters[] = 'Hasta: ' . Carbon::parse($request->date_to)->format('d/m/Y');
        }
        if ($request->has('warehouse_id')) {
            $filters[] = 'Almacén: ' . $request->warehouse_id;
        }
        if ($request->has('seller_id')) {
            $filters[] = 'Vendedor: ' . $request->seller_id;
        }

        return !empty($filters) ? implode(' | ', $filters) : 'Sin filtros';
    }
}
