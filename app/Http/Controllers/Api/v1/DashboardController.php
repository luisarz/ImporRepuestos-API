<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\SalesHeader;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Batch;
use App\Models\InventoriesBatch;
use App\Models\Customer;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener todas las métricas del dashboard
     */
    public function getMetrics()
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $startOfMonth = Carbon::now()->startOfMonth();
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

            // Ventas de hoy
            $todaySales = SalesHeader::whereDate('sale_date', $today)
                ->where('sale_status', 2) // Finalizadas
                ->sum('sale_total');

            // Ventas de ayer
            $yesterdaySales = SalesHeader::whereDate('sale_date', $yesterday)
                ->where('sale_status', 2)
                ->sum('sale_total');

            // Calcular cambio porcentual
            $todayChange = $yesterdaySales > 0
                ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
                : 0;

            // Items vendidos hoy
            $todayItemsSold = SaleItem::whereHas('sale', function($query) use ($today) {
                $query->whereDate('sale_date', $today)
                      ->where('sale_status', 2);
            })->sum('quantity');

            // Ticket promedio
            $todaySalesCount = SalesHeader::whereDate('sale_date', $today)
                ->where('sale_status', 2)
                ->count();
            $averageTicket = $todaySalesCount > 0 ? $todaySales / $todaySalesCount : 0;

            // Ventas del mes
            $monthSales = SalesHeader::whereBetween('sale_date', [$startOfMonth, $today])
                ->where('sale_status', 2)
                ->sum('sale_total');

            // Ventas del mes anterior
            $lastMonthSales = SalesHeader::whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
                ->where('sale_status', 2)
                ->sum('sale_total');

            // Calcular cambio porcentual mensual
            $monthChange = $lastMonthSales > 0
                ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100
                : 0;

            // Productos bajo stock
            $lowStock = Inventory::whereRaw('stock_actual_quantity < stock_min')
                ->where('stock_min', '>', 0)
                ->count();

            // Lotes próximos a vencer (30 días)
            $expiringBatches = Batch::where('expiration_date', '<=', Carbon::now()->addDays(30))
                ->where('expiration_date', '>=', Carbon::now())
                ->count();

            // DTEs pendientes
            $pendingDTE = SalesHeader::where('sale_status', 2)
                ->where('is_dte', 0)
                ->count();

            // Nuevos clientes del mes
            $newCustomers = Customer::whereBetween('created_at', [$startOfMonth, Carbon::now()])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'today_sales' => round($todaySales, 2),
                    'today_change' => round($todayChange, 2),
                    'today_items_sold' => $todayItemsSold,
                    'average_ticket' => round($averageTicket, 2),
                    'month_sales' => round($monthSales, 2),
                    'month_change' => round($monthChange, 2),
                    'low_stock' => $lowStock,
                    'expiring_batches' => $expiringBatches,
                    'pending_dte' => $pendingDTE,
                    'new_customers' => $newCustomers
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getMetrics: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas del día
     */
    public function getTodaySales()
    {
        try {
            $today = Carbon::today();

            $sales = SalesHeader::with(['customer', 'seller'])
                ->whereDate('sale_date', $today)
                ->where('sale_status', 2)
                ->orderBy('created_at', 'desc')
                ->get();

            $total = $sales->sum('sale_total');
            $count = $sales->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'sales' => $sales,
                    'total' => round($total, 2),
                    'count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getTodaySales: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas del día: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas del mes
     */
    public function getMonthSales()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $today = Carbon::now();

            $sales = SalesHeader::whereBetween('sale_date', [$startOfMonth, $today])
                ->where('sale_status', 2)
                ->get();

            $total = $sales->sum('sale_total');
            $count = $sales->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => round($total, 2),
                    'count' => $count,
                    'average' => $count > 0 ? round($total / $count, 2) : 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getMonthSales: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas del mes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos más vendidos
     */
    public function getTopProducts(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $period = $request->input('period', 'month'); // today, week, month

            $startDate = match($period) {
                'today' => Carbon::today(),
                'week' => Carbon::now()->subDays(7),
                'month' => Carbon::now()->subDays(30),
                default => Carbon::now()->subDays(30)
            };

            $topProducts = SaleItem::select(
                    'products.id',
                    'products.code',
                    'products.description as name',
                    DB::raw('SUM(sale_items.quantity) as sold'),
                    DB::raw('SUM(sale_items.total) as revenue')
                )
                ->join('sales_headers', 'sale_items.sale_id', '=', 'sales_headers.id')
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->where('sales_headers.sale_status', 2)
                ->where('sales_headers.sale_date', '>=', $startDate)
                ->groupBy('products.id', 'products.code', 'products.description')
                ->orderBy('revenue', 'desc')
                ->limit($limit)
                ->get();

            // Calcular porcentaje de ingresos
            $totalRevenue = $topProducts->sum('revenue');

            $topProducts = $topProducts->map(function($product) use ($totalRevenue) {
                $product->revenue_percentage = $totalRevenue > 0
                    ? round(($product->revenue / $totalRevenue) * 100, 1)
                    : 0;
                $product->revenue = round($product->revenue, 2);
                return $product;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $topProducts,
                    'total_revenue' => round($totalRevenue, 2)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getTopProducts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más vendidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para gráfico de ventas
     */
    public function getSalesChart(Request $request)
    {
        try {
            $period = $request->input('period', 'week'); // week, month, year

            if ($period === 'week') {
                // Últimos 7 días
                $labels = [];
                $values = [];

                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $labels[] = $date->locale('es')->isoFormat('ddd');

                    $sales = SalesHeader::whereDate('sale_date', $date)
                        ->where('sale_status', 2)
                        ->sum('sale_total');

                    $values[] = round($sales, 2);
                }

            } elseif ($period === 'month') {
                // Últimos 30 días
                $labels = [];
                $values = [];

                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $labels[] = $date->format('d');

                    $sales = SalesHeader::whereDate('sale_date', $date)
                        ->where('sale_status', 2)
                        ->sum('sale_total');

                    $values[] = round($sales, 2);
                }

            } else { // year
                // Últimos 12 meses
                $labels = [];
                $values = [];

                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $labels[] = $date->locale('es')->isoFormat('MMM');

                    $sales = SalesHeader::whereYear('sale_date', $date->year)
                        ->whereMonth('sale_date', $date->month)
                        ->where('sale_status', 2)
                        ->sum('sale_total');

                    $values[] = round($sales, 2);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getSalesChart: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener gráfico de ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas recientes
     */
    public function getRecentSales(Request $request)
    {
        try {
            $limit = $request->input('limit', 5);

            $sales = SalesHeader::with(['customer', 'seller', 'documentType'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'sales' => $sales
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getRecentSales: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas recientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getLowStockProducts()
    {
        try {
            $products = Inventory::with(['product', 'warehouse'])
                ->whereRaw('stock_actual_quantity < stock_min')
                ->where('stock_min', '>', 0)
                ->orderBy('stock_actual_quantity', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'count' => $products->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getLowStockProducts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos con stock bajo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lotes próximos a vencer
     */
    public function getExpiringBatches(Request $request)
    {
        try {
            $days = $request->input('days', 30);

            $batches = InventoriesBatch::with(['batch', 'inventory.product', 'inventory.warehouse'])
                ->whereHas('batch', function($query) use ($days) {
                    $query->where('expiration_date', '<=', Carbon::now()->addDays($days))
                          ->where('expiration_date', '>=', Carbon::now());
                })
                ->where('quantity', '>', 0)
                ->get()
                ->sortBy('batch.expiration_date');

            return response()->json([
                'success' => true,
                'data' => [
                    'batches' => $batches,
                    'count' => $batches->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getExpiringBatches: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener lotes próximos a vencer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener DTEs pendientes
     */
    public function getPendingDTEs()
    {
        try {
            $pendingDTEs = SalesHeader::with(['customer', 'warehouse'])
                ->where('sale_status', 2) // Finalizadas
                ->where('is_dte', 0) // Sin DTE generado
                ->orderBy('sale_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'dtes' => $pendingDTEs,
                    'count' => $pendingDTEs->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getPendingDTEs: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener DTEs pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas por categoría
     */
    public function getSalesByCategory()
    {
        try {
            $salesByCategory = SaleItem::select(
                    'categories.description as category_name',
                    DB::raw('SUM(sale_items.total) as total')
                )
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('sales_headers', 'sale_items.sale_id', '=', 'sales_headers.id')
                ->where('sales_headers.sale_status', 2)
                ->where('sales_headers.sale_date', '>=', Carbon::now()->subDays(30))
                ->groupBy('categories.id', 'categories.description')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            $labels = $salesByCategory->pluck('category_name')->toArray();
            $values = $salesByCategory->pluck('total')->map(function($value) {
                return round($value, 2);
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getSalesByCategory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas por categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clientes nuevos del mes
     */
    public function getNewCustomers()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();

            $newCustomers = Customer::whereBetween('created_at', [$startOfMonth, Carbon::now()])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $newCustomers,
                    'count' => $newCustomers->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getNewCustomers: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes nuevos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener alertas del sistema
     */
    public function getAlerts()
    {
        try {
            $alerts = [];

            // Verificar stock bajo
            $lowStockCount = Inventory::whereRaw('stock_actual_quantity < stock_min')
                ->where('stock_min', '>', 0)
                ->count();

            if ($lowStockCount > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Stock Bajo',
                    'message' => "Tienes {$lowStockCount} productos con stock crítico",
                    'action' => 'low-stock',
                    'priority' => 'high'
                ];
            }

            // Verificar lotes por vencer
            $expiringBatchesCount = Batch::where('expiration_date', '<=', Carbon::now()->addDays(30))
                ->where('expiration_date', '>=', Carbon::now())
                ->count();

            if ($expiringBatchesCount > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Lotes por Vencer',
                    'message' => "Tienes {$expiringBatchesCount} lotes próximos a vencer",
                    'action' => 'expiring-batches',
                    'priority' => 'high'
                ];
            }

            // Verificar DTEs pendientes
            $pendingDTECount = SalesHeader::where('sale_status', 2)
                ->where('is_dte', 0)
                ->count();

            if ($pendingDTECount > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'DTEs Pendientes',
                    'message' => "Hay {$pendingDTECount} ventas sin facturación electrónica",
                    'action' => 'pending-dte',
                    'priority' => 'medium'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'alerts' => $alerts,
                    'count' => count($alerts)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DashboardController::getAlerts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas: ' . $e->getMessage()
            ], 500);
        }
    }
}
