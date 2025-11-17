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
    // ========== REPORTES DE VENTAS ==========

    /**
     * Reporte de ventas por período
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $query = SalesHeader::with(['customer', 'seller', 'warehouse'])
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $salesBySeller = SalesHeader::select(
                    'seller_id',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(sale_total) as total_amount'),
                    DB::raw('AVG(sale_total) as average_ticket')
                )
                ->with('seller')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $salesByCustomer = SalesHeader::select(
                    'customer_id',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(sale_total) as total_amount'),
                    DB::raw('AVG(sale_total) as average_ticket'),
                    DB::raw('MAX(sale_date) as last_sale_date')
                )
                ->with('customer')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $salesByProduct = SaleItem::select(
                    'id_product',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(subtotal) as total_amount'),
                    DB::raw('AVG(unit_price) as average_price'),
                    DB::raw('COUNT(DISTINCT id_sale) as sales_count')
                )
                ->with('product')
                ->whereHas('salesHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sale_date', [$dateFrom, $dateTo])
                          ->where('sale_status', '2');
                })
                ->groupBy('id_product')
                ->orderBy('total_amount', 'desc')
                ->get();

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $salesByPayment = SalePaymentDetail::select(
                    'payment_method_id',
                    DB::raw('COUNT(DISTINCT sale_id) as total_sales'),
                    DB::raw('SUM(payment_amount) as total_amount'),
                    DB::raw('AVG(payment_amount) as average_amount')
                )
                ->with('paymentMethod')
                ->whereHas('sale', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sale_date', [$dateFrom, $dateTo])
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $query = PurchasesHeader::with(['provider', 'warehouse'])
                ->whereBetween('purchase_date', [$dateFrom, $dateTo]);

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $purchasesByProvider = PurchasesHeader::select(
                    'provider_id',
                    DB::raw('COUNT(*) as total_purchases'),
                    DB::raw('SUM(total_purchase) as total_amount'),
                    DB::raw('AVG(total_purchase) as average_purchase'),
                    DB::raw('MAX(purchase_date) as last_purchase_date')
                )
                ->with('provider')
                ->whereBetween('purchase_date', [$dateFrom, $dateTo])
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $purchasesByProduct = PurchaseItem::select(
                    'id_product',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(subtotal) as total_amount'),
                    DB::raw('AVG(unit_price) as average_price'),
                    DB::raw('COUNT(DISTINCT id_purchase) as purchases_count')
                )
                ->with('product')
                ->whereHas('purchaseHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
                })
                ->groupBy('id_product')
                ->orderBy('total_amount', 'desc')
                ->get();

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
            $query = Inventory::with(['product', 'warehouse'])
                ->where('quantity', '>', 0);

            if ($request->has('warehouse_id')) {
                $query->where('id_warehouse', $request->input('warehouse_id'));
            }

            $inventories = $query->get()->map(function ($inventory) {
                $product = $inventory->product;
                $costPrice = $product->cost_price ?? 0;
                $salePrice = $product->sale_price ?? 0;
                $totalCost = $inventory->quantity * $costPrice;
                $totalSale = $inventory->quantity * $salePrice;
                $potentialProfit = $totalSale - $totalCost;

                return [
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'warehouse' => $inventory->warehouse->name,
                    'quantity' => $inventory->quantity,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'total_cost' => $totalCost,
                    'total_sale_value' => $totalSale,
                    'potential_profit' => $potentialProfit,
                ];
            });

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
            $dateFrom = $request->input('date_from', Carbon::now()->subMonths(3));
            $dateTo = $request->input('date_to', Carbon::now());
            $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));

            // Obtener ventas por producto en el período
            $salesByProduct = SaleItem::select(
                    'id_product',
                    DB::raw('SUM(quantity) as total_sold')
                )
                ->whereHas('salesHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sale_date', [$dateFrom, $dateTo])
                          ->where('sale_status', '2');
                })
                ->groupBy('id_product')
                ->pluck('total_sold', 'id_product');

            // Obtener inventario actual
            $inventories = Inventory::with(['product'])
                ->where('quantity', '>', 0)
                ->get()
                ->map(function ($inventory) use ($salesByProduct, $days) {
                    $productId = $inventory->id_product;
                    $sold = $salesByProduct[$productId] ?? 0;
                    $avgStock = $inventory->quantity;
                    $rotation = $avgStock > 0 ? ($sold / $avgStock) : 0;
                    $daysToSellout = $sold > 0 ? ($avgStock / $sold) * $days : 999;

                    return [
                        'product_code' => $inventory->product->code,
                        'product_name' => $inventory->product->name,
                        'current_stock' => $inventory->quantity,
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
            $inventories = Inventory::with(['product'])
                ->where('quantity', '>', 0)
                ->get();

            $productsWithoutMovement = [];

            foreach ($inventories as $inventory) {
                // Buscar última venta del producto
                $lastSale = SaleItem::where('id_product', $inventory->id_product)
                    ->whereHas('salesHeader', function($query) {
                        $query->where('sale_status', '2');
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Si no hay venta o la última venta es anterior a la fecha límite
                if (!$lastSale || $lastSale->created_at < $dateLimit) {
                    $daysSinceLastSale = $lastSale ?
                        Carbon::parse($lastSale->created_at)->diffInDays(Carbon::now()) : 999;

                    $productsWithoutMovement[] = [
                        'product_code' => $inventory->product->code,
                        'product_name' => $inventory->product->name,
                        'current_stock' => $inventory->quantity,
                        'cost_price' => $inventory->product->cost_price ?? 0,
                        'inventory_value' => $inventory->quantity * ($inventory->product->cost_price ?? 0),
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
                    'id_warehouse',
                    DB::raw('COUNT(DISTINCT id_product) as total_products'),
                    DB::raw('SUM(quantity) as total_quantity')
                )
                ->with('warehouse')
                ->where('quantity', '>', 0)
                ->groupBy('id_warehouse')
                ->get()
                ->map(function ($item) {
                    return [
                        'warehouse_name' => $item->warehouse->name,
                        'total_products' => $item->total_products,
                        'total_quantity' => $item->total_quantity,
                    ];
                });

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
                ->whereRaw('quantity < minimum_stock')
                ->orWhere('quantity', '=', 0)
                ->get()
                ->map(function ($inventory) {
                    return [
                        'product_code' => $inventory->product->code,
                        'product_name' => $inventory->product->name,
                        'warehouse' => $inventory->warehouse->name,
                        'current_stock' => $inventory->quantity,
                        'minimum_stock' => $inventory->minimum_stock,
                        'deficit' => $inventory->minimum_stock - $inventory->quantity,
                        'status' => $inventory->quantity == 0 ? 'Sin Stock' : 'Bajo Mínimo',
                    ];
                });

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $profitability = SaleItem::select(
                    'id_product',
                    DB::raw('SUM(quantity) as total_sold'),
                    DB::raw('SUM(subtotal) as total_revenue'),
                    DB::raw('SUM(quantity * cost_price) as total_cost'),
                    DB::raw('SUM(subtotal - (quantity * cost_price)) as total_profit')
                )
                ->with('product')
                ->whereHas('salesHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sale_date', [$dateFrom, $dateTo])
                          ->where('sale_status', '2');
                })
                ->groupBy('id_product')
                ->orderBy('total_profit', 'desc')
                ->get()
                ->map(function ($item) {
                    $marginPercent = $item->total_revenue > 0 ?
                        (($item->total_profit / $item->total_revenue) * 100) : 0;

                    return [
                        'product_code' => $item->product->code,
                        'product_name' => $item->product->name,
                        'total_sold' => $item->total_sold,
                        'total_revenue' => $item->total_revenue,
                        'total_cost' => $item->total_cost,
                        'total_profit' => $item->total_profit,
                        'profit_margin_percent' => round($marginPercent, 2),
                    ];
                });

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $profitabilityByCategory = SaleItem::select(
                    'products.id_category',
                    DB::raw('SUM(sale_items.quantity) as total_sold'),
                    DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                    DB::raw('SUM(sale_items.quantity * sale_items.cost_price) as total_cost'),
                    DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.cost_price)) as total_profit')
                )
                ->join('products', 'sale_items.id_product', '=', 'products.id')
                ->join('categories', 'products.id_category', '=', 'categories.id')
                ->whereHas('salesHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('sale_date', [$dateFrom, $dateTo])
                          ->where('sale_status', '2');
                })
                ->with('product.category')
                ->groupBy('products.id_category')
                ->orderBy('total_profit', 'desc')
                ->get();

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $sales = SalesHeader::with('saleDetails')
                ->whereBetween('sale_date', [$dateFrom, $dateTo])
                ->where('sale_status', '2')
                ->get();

            $data = $sales->map(function ($sale) {
                $totalCost = $sale->saleDetails->sum(function ($item) {
                    return $item->quantity * $item->cost_price;
                });
                $totalRevenue = $sale->sale_total;
                $profit = $totalRevenue - $totalCost;
                $marginPercent = $totalRevenue > 0 ? (($profit / $totalRevenue) * 100) : 0;

                return [
                    'sale_id' => $sale->id,
                    'date' => $sale->sale_date,
                    'customer' => $sale->customer->name ?? 'N/A',
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'margin_percent' => round($marginPercent, 2),
                ];
            });

            $summary = [
                'total_sales' => $data->count(),
                'total_revenue' => $data->sum('total_revenue'),
                'total_cost' => $data->sum('total_cost'),
                'total_profit' => $data->sum('profit'),
                'average_margin' => $data->avg('margin_percent'),
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $dtes = HistoryDte::with('salesHeader.customer')
                ->whereBetween('fecha_emision', [$dateFrom, $dateTo])
                ->orderBy('fecha_emision', 'desc')
                ->get();

            $summary = [
                'total_dtes' => $dtes->count(),
                'approved' => $dtes->where('status', 'APROBADO')->count(),
                'rejected' => $dtes->where('status', 'RECHAZADO')->count(),
                'observed' => $dtes->where('status', 'OBSERVADO')->count(),
                'pending' => $dtes->where('status', 'PENDIENTE')->count(),
                'total_amount' => $dtes->where('status', 'APROBADO')->sum('total_purchase'),
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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $dtesByStatus = HistoryDte::select(
                    'status',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(total_purchase) as total_amount')
                )
                ->whereBetween('fecha_emision', [$dateFrom, $dateTo])
                ->groupBy('status')
                ->get();

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
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->input('date_to', Carbon::now());

            $rejectedDTEs = HistoryDte::with('salesHeader.customer')
                ->whereBetween('fecha_emision', [$dateFrom, $dateTo])
                ->where('status', 'RECHAZADO')
                ->orderBy('fecha_emision', 'desc')
                ->get();

            $summary = [
                'total_rejected' => $rejectedDTEs->count(),
                'total_amount' => $rejectedDTEs->sum('total_purchase'),
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
