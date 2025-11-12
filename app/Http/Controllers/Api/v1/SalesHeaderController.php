<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\SalesHeaderCollection;
use App\Http\Resources\Api\v1\SalesHeaderResource;
use App\Models\SalesHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\InventoryService;
use App\Services\KardexService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SalesHeaderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $filtersJson = $request->input('filters') ?? '[]';
            $filters = json_decode($filtersJson, true) ?? [];

            $query = SalesHeader::with([
                'customer:id,document_number,name,last_name,document_type_id',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
                'documentType',
                'paymentMethod',
                'saleCondition',
            ]);

            // Búsqueda general
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    // Buscar en campos de la venta
                    $q->where('id', 'like', "%$search%")
                        ->orWhere('document_internal_number', 'like', "%$search%")
                        ->orWhere('sale_total', 'like', "%$search%")
                        // Buscar en cliente
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%")
                                ->orWhere('document_number', 'like', "%$search%");
                        })
                        // Buscar en vendedor
                        ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                            $sellerQuery->where('name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%");
                        })
                        // Buscar en almacén
                        ->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {
                            $warehouseQuery->where('name', 'like', "%$search%");
                        });
                });
            }

            // Aplicar filtros
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if (isset($filter['field']) && isset($filter['value'])) {
                        $field = $filter['field'];
                        $value = $filter['value'];
                        $operator = $filter['operator'] ?? '=';

                        // Filtros específicos
                        if ($field === 'sale_status' && !empty($value)) {
                            $query->where('sale_status', $value);
                        } elseif ($field === 'customer_id' && !empty($value)) {
                            $query->where('customer_id', $value);
                        } elseif ($field === 'warehouse_id' && !empty($value)) {
                            $query->where('warehouse_id', $value);
                        } elseif ($field === 'seller_id' && !empty($value)) {
                            $query->where('seller_id', $value);
                        } elseif ($field === 'payment_status' && !empty($value)) {
                            $query->where('payment_status', $value);
                        } elseif ($field === 'is_dte' && $value !== '') {
                            $query->where('is_dte', $value);
                        } elseif ($field === 'date_from' && !empty($value)) {
                            $query->whereDate('sale_date', '>=', $value);
                        } elseif ($field === 'date_to' && !empty($value)) {
                            $query->whereDate('sale_date', '<=', $value);
                        } elseif ($field === 'min_total' && !empty($value)) {
                            $query->where('sale_total', '>=', $value);
                        } elseif ($field === 'max_total' && !empty($value)) {
                            $query->where('sale_total', '<=', $value);
                        }
                    }
                }
            }

            // Ordenar por fecha más reciente primero
            $query->orderBy('sale_date', 'desc')->orderBy('id', 'desc');

            $salesHeaders = $query->paginate($perPage);

            $salesHeaders->getCollection()->transform(function ($sale) {
                $sale->formatted_date = $sale->sale_date->format('d/m/Y');
                $sale->total_sale_formatted = number_format($sale->sale_total, 2, '.', ',');
                return $sale;
            });

            return ApiResponse::success($salesHeaders, 'Ventas recuperadas con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(SalesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Validar que exista una caja abierta en la sucursal
            $warehouseId = $validated['warehouse_id'];
            $openCashRegister = \App\Models\CashRegister::where('warehouse_id', $warehouseId)
                ->where('is_active', 1)
                ->whereHas('currentOpening')
                ->with('currentOpening')
                ->first();

            if (!$openCashRegister) {
                return ApiResponse::error(
                    null,
                    'No hay una caja registradora abierta en esta sucursal. Por favor, abra una caja antes de realizar ventas.',
                    400
                );
            }

            // Asignar el cashbox_open_id automáticamente
            $validated['cashbox_open_id'] = $openCashRegister->currentOpening->id;

            $salesHeader = SalesHeader::create($validated);
            return ApiResponse::success($salesHeader, 'Venta creada con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show($id): JsonResponse
    {
        \Illuminate\Log\log($id);
        try {
//            $salesHeader = SalesHeader::with(['customer:id,document_number,name,last_name,sales_type',
//                'warehouse:id,name',
//                'seller:id,name,last_name,dui',
//                'items',
//                'items.inventory:id,code,name',
//            ])->findOrFail($id);
            $salesHeader = SalesHeader::findOrFail($id);
            return ApiResponse::success($salesHeader, 'Venta recuperada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(SalesHeaderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::findOrFail($id);
            $salesHeader->update($request->validated());
            return ApiResponse::success($salesHeader, 'Venta actualizada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::findOrFail($id);
            $salesHeader->delete();
            return ApiResponse::success(null, 'Venta eliminada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
    public function finalize($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Buscar la venta
            $sale = SalesHeader::with('saleDetails')->findOrFail($id);

            // Verificar que la venta esté en estado IN_PROGRESS (1)
            if ($sale->sale_status != 1) {
                return ApiResponse::error(
                    'La venta debe estar en estado EN PROGRESO para ser finalizada',
                    'Estado inválido',
                    400
                );
            }

            // Verificar que la venta tenga items
            if ($sale->saleDetails->isEmpty()) {
                return ApiResponse::error(
                    'La venta no tiene items para finalizar',
                    'Venta sin items',
                    400
                );
            }

            $inventoryService = new InventoryService();
            $kardexService = new KardexService();

            $processedItems = [];
            $errors = [];

            // Procesar cada item de la venta
            foreach ($sale->saleDetails as $saleItem) {
                try {
                    // Si no tiene lote asignado, asignar automáticamente el más antiguo (FIFO)

                    if (!$saleItem->batch_id) {
                        $saleItem = $inventoryService->assignOldestBatch($saleItem);
                    }
                    // Descontar stock del lote especificado
                    $inventoryService->decreaseStock($saleItem);

                    // Registrar movimiento en kardex
                    $kardexService->registerSaleMovement($saleItem);

                    $processedItems[] = [
                        'sale_item_id' => $saleItem->id,
                        'inventory_id' => $saleItem->inventory_id,
                        'batch_id' => $saleItem->batch_id,
                        'quantity' => $saleItem->quantity,
                        'status' => 'success'
                    ];

                } catch (\Exception $e) {
                    Log::error("Error procesando item de venta", [
                        'sale_item_id' => $saleItem->id,
                        'error' => $e->getMessage()
                    ]);

                    $errors[] = [
                        'sale_item_id' => $saleItem->id,
                        'inventory_id' => $saleItem->inventory_id,
                        'batch_id' => $saleItem->batch_id,
                        'error' => $e->getMessage()
                    ];

                    // Si hay algún error, hacer rollback completo
                    DB::rollBack();

                    return ApiResponse::error(
                        $e->getMessage(),
                        'Error al finalizar venta',
                        500,
                        ['processed_items' => $processedItems, 'errors' => $errors]
                    );
                }
            }

            // Actualizar estado de la venta a INVOICED (2)
            $sale->sale_status = 2;
            $sale->save();

            DB::commit();

            Log::info("Venta finalizada exitosamente", [
                'sale_id' => $id,
                'items_processed' => count($processedItems),
                'total' => $sale->sale_total
            ]);

            return ApiResponse::success([
                'sale' => $sale,
                'processed_items' => $processedItems
            ], 'Venta finalizada con éxito', 200);

        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al finalizar venta: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al finalizar la venta', 500);
        }
    }

    /**
     * Anular venta: devuelve inventario a los lotes y registra en kardex
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Buscar la venta
            $sale = SalesHeader::with('saleDetails')->findOrFail($id);

            // Verificar que la venta esté en estado INVOICED (2)
            if ($sale->sale_status != 2) {
                return ApiResponse::error(
                    'Solo se pueden anular ventas en estado FACTURADA',
                    'Estado inválido',
                    400
                );
            }

            $inventoryService = new InventoryService();
            $kardexService = new KardexService();

            $processedItems = [];
            $errors = [];

            // Procesar cada item de la venta
            foreach ($sale->saleDetails as $saleItem) {
                try {
                    // Devolver stock al lote del que salió
                    $inventoryService->increaseStock($saleItem);

                    // Registrar movimiento de anulación en kardex
                    $kardexService->registerSaleCancellation($saleItem);

                    $processedItems[] = [
                        'sale_item_id' => $saleItem->id,
                        'inventory_id' => $saleItem->inventory_id,
                        'batch_id' => $saleItem->batch_id,
                        'quantity' => $saleItem->quantity,
                        'status' => 'returned'
                    ];

                } catch (\Exception $e) {
                    Log::error("Error anulando item de venta", [
                        'sale_item_id' => $saleItem->id,
                        'error' => $e->getMessage()
                    ]);

                    $errors[] = [
                        'sale_item_id' => $saleItem->id,
                        'error' => $e->getMessage()
                    ];

                    // Si hay algún error, hacer rollback completo
                    DB::rollBack();

                    return ApiResponse::error(
                        $e->getMessage(),
                        'Error al anular venta',
                        500,
                        ['processed_items' => $processedItems, 'errors' => $errors]
                    );
                }
            }

            // Actualizar estado de la venta a INVALIDATED (3)
            $sale->sale_status = 3;
            $sale->save();

            DB::commit();

            Log::info("Venta anulada exitosamente", [
                'sale_id' => $id,
                'items_returned' => count($processedItems)
            ]);

            return ApiResponse::success([
                'sale' => $sale,
                'processed_items' => $processedItems
            ], 'Venta anulada con éxito. Inventario devuelto a los lotes originales.', 200);

        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al anular venta: " . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al anular la venta', 500);
        }
    }
    /**

     * Obtener estadísticas de ventas

     */

    public function stats(): JsonResponse

    {

        try {

            $total = SalesHeader::count();

            $today = SalesHeader::whereDate('sale_date', today())->count();

            $pending = SalesHeader::where('sale_status', 1)->count(); // IN_PROGRESS

            $completed = SalesHeader::where('sale_status', 2)->count(); // INVOICED



            return ApiResponse::success([

                'total' => $total,

                'today' => $today,

                'pending' => $pending,

                'completed' => $completed

            ], 'Estadísticas obtenidas exitosamente');

        } catch (\Exception $e) {

            Log::error("Error al obtener estadísticas de ventas: " . $e->getMessage());

            return ApiResponse::error(null, 'Error al obtener estadísticas', 500);

        }

    }
}
