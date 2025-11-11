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

            $query = SalesHeader::with(['customer', 'employee', 'warehouse'])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'FINALIZADA');

            // Filtros adicionales
            if ($request->has('seller_id')) {
                $query->where('id_employee', $request->input('seller_id'));
            }

            if ($request->has('customer_id')) {
                $query->where('id_customer', $request->input('customer_id'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            $sales = $query->orderBy('date', 'desc')->get();

            // Calcular resumen
            $summary = [
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('total'),
                'average_ticket' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
                'total_items' => $sales->sum(function ($sale) {
                    return $sale->saleItems->sum('quantity');
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
                    'id_employee',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(total) as total_amount'),
                    DB::raw('AVG(total) as average_ticket')
                )
                ->with('employee')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'FINALIZADA')
                ->groupBy('id_employee')
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
                    'id_customer',
                    DB::raw('COUNT(*) as total_sales'),
                    DB::raw('SUM(total) as total_amount'),
                    DB::raw('AVG(total) as average_ticket'),
                    DB::raw('MAX(date) as last_sale_date')
                )
                ->with('customer')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'FINALIZADA')
                ->groupBy('id_customer')
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
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                          ->where('status', 'FINALIZADA');
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
                    'id_payment_method',
                    DB::raw('COUNT(DISTINCT id_sale) as total_sales'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('AVG(amount) as average_amount')
                )
                ->with('paymentMethod')
                ->whereHas('salesHeader', function($query) use ($dateFrom, $dateTo) {
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                          ->where('status', 'FINALIZADA');
                })
                ->groupBy('id_payment_method')
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
                ->whereBetween('date', [$dateFrom, $dateTo]);

            if ($request->has('provider_id')) {
                $query->where('id_provider', $request->input('provider_id'));
            }

            $purchases = $query->orderBy('date', 'desc')->get();

            $summary = [
                'total_purchases' => $purchases->count(),
                'total_amount' => $purchases->sum('total'),
                'average_purchase' => $purchases->count() > 0 ? $purchases->sum('total') / $purchases->count() : 0,
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
                    'id_provider',
                    DB::raw('COUNT(*) as total_purchases'),
                    DB::raw('SUM(total) as total_amount'),
                    DB::raw('AVG(total) as average_purchase'),
                    DB::raw('MAX(date) as last_purchase_date')
                )
                ->with('provider')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->groupBy('id_provider')
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
                    $query->whereBetween('date', [$dateFrom, $dateTo]);
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
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                          ->where('status', 'FINALIZADA');
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
                        $query->where('status', 'FINALIZADA');
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
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                          ->where('status', 'FINALIZADA');
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
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                          ->where('status', 'FINALIZADA');
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

            $sales = SalesHeader::with('saleItems')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('status', 'FINALIZADA')
                ->get();

            $data = $sales->map(function ($sale) {
                $totalCost = $sale->saleItems->sum(function ($item) {
                    return $item->quantity * $item->cost_price;
                });
                $totalRevenue = $sale->total;
                $profit = $totalRevenue - $totalCost;
                $marginPercent = $totalRevenue > 0 ? (($profit / $totalRevenue) * 100) : 0;

                return [
                    'sale_id' => $sale->id,
                    'date' => $sale->date,
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
                'total_amount' => $dtes->where('status', 'APROBADO')->sum('total'),
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
                    DB::raw('SUM(total) as total_amount')
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
                'total_amount' => $rejectedDTEs->sum('total'),
            ];

            return ApiResponse::success([
                'summary' => $summary,
                'data' => $rejectedDTEs
            ], 'Reporte de DTEs rechazados', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar el reporte', 500);
        }
    }
}
