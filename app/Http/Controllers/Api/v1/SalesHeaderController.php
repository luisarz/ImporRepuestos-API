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
                        } elseif ($field === 'exclude_status' && !empty($value)) {
                            $query->where('sale_status', '!=', $value);
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

                // Formatear número de venta con tipo de documento + número de control interno
                // Si document_internal_number es 0, mostrar "Sin asignar"
                // Ejemplos: "Factura F-000001", "CCF CCF-000123", "Sin asignar"

                // Si el número de control interno es 0, significa que aún no se ha generado el DTE
                if ($sale->document_internal_number == 0) {
                    $sale->sale_number_formatted = 'Sin asignar';
                    $sale->document_type_name = $sale->documentType ? $sale->documentType->name : 'Documento';
                } else {
                    $prefix = 'DOC'; // fallback si no se encuentra correlativo

                    // Obtener el prefix del correlativo activo para este tipo de documento
                    if ($sale->warehouse && $sale->document_type_id) {
                        $cashRegister = $sale->warehouse->cashRegisters()->where('is_active', 1)->first();
                        if ($cashRegister) {
                            $correlativo = \App\Models\Correlative::where('cash_register_id', $cashRegister->id)
                                ->where('document_type_id', $sale->document_type_id)
                                ->where('is_active', true)
                                ->first();
                            if ($correlativo) {
                                // Usar el prefix del correlativo (ej: "F-", "CCF-")
                                // Remover guión si ya lo tiene para evitar duplicación
                                $prefix = rtrim($correlativo->prefix, '-');
                            }
                        }
                    }

                    // Formato: "Tipo de Documento PREFIX-NÚMERO"
                    $documentTypeName = $sale->documentType ? $sale->documentType->name : 'Documento';
                    $controlNumber = $prefix . '-' . str_pad($sale->document_internal_number, 4, '0', STR_PAD_LEFT);

                    $sale->sale_number_formatted = $controlNumber;
                    $sale->document_type_name = $documentTypeName;
                }

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
            DB::beginTransaction();

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

            // IMPORTANTE: Siempre forzar document_internal_number a 0 al crear una venta
            // Ignorar cualquier valor que venga del frontend
            // Este número se asignará definitivamente cuando se envíe el DTE desde el correlativo
            $validated['document_internal_number'] = 0;

            // ===== SISTEMA DE PAGOS DIVIDIDOS =====
            // Si vienen payment_details, procesarlos
            $paymentDetails = $validated['payment_details'] ?? null;
            unset($validated['payment_details']); // Remover del array para no intentar insertarlo en sales_headers

            // Si hay pagos divididos, determinar el método principal para el DTE
            // ===== IMPORTANTE: Verificar si es venta a crédito PRIMERO =====
            // operation_condition_id: 1=Contado, 2=A crédito, 3=Otro
            $isCredit = isset($validated['operation_condition_id']) && $validated['operation_condition_id'] == 2;

            if ($isCredit) {
                // ===== VENTA A CRÉDITO =====
                // IMPORTANTE: Hacienda requiere payment_method_id incluso para crédito
                // El payment_method_id indica cómo se pagará eventualmente
                // Siempre payment_status = 0 (Sin Pago - porque no se paga de inmediato)
                $validated['payment_status'] = 0;

                // No registrar pagos (no aplica para crédito inicial)
                $paymentDetails = [];
            } else {
                // ===== VENTA DE CONTADO U OTRA =====
                if ($paymentDetails && is_array($paymentDetails) && count($paymentDetails) > 0) {
                    // Validar que la suma de pagos coincida con el total de venta
                    $totalPayments = array_reduce($paymentDetails, function($carry, $payment) {
                        return $carry + ($payment['amount'] ?? 0);
                    }, 0);

                    if (abs($totalPayments - $validated['sale_total']) > 0.01) {
                        DB::rollBack();
                        return ApiResponse::error(
                            ['payment_mismatch' => "La suma de pagos ($" . number_format($totalPayments, 2) . ") no coincide con el total de venta ($" . number_format($validated['sale_total'], 2) . ")"],
                            'La suma de los métodos de pago no coincide con el total de la venta',
                            422
                        );
                    }

                    // Determinar el método principal para DTE:
                    // Prioridad: 1) Efectivo si existe, 2) El de mayor monto
                    $mainPayment = null;
                    $cashPayment = null;

                    foreach ($paymentDetails as $payment) {
                        // Buscar si hay efectivo (código 01 según catálogo MH)
                        $paymentMethod = \App\Models\PaymentMethod::find($payment['payment_method_id']);
                        if ($paymentMethod && $paymentMethod->code === '01') {
                            $cashPayment = $payment;
                            break;
                        }

                        // Encontrar el de mayor monto
                        if (!$mainPayment || $payment['amount'] > $mainPayment['amount']) {
                            $mainPayment = $payment;
                        }
                    }

                    // Usar efectivo si existe, si no, el de mayor monto
                    $primaryPayment = $cashPayment ?? $mainPayment;
                    $validated['payment_method_id'] = $primaryPayment['payment_method_id'];

                    // Payment status = 3 (Pagado) porque tiene pagos
                    $validated['payment_status'] = 3;
                } else {
                    // No tiene pagos = 1 (Pendiente)
                    $validated['payment_status'] = 1;
                }
            }

            // Crear la venta
            $salesHeader = SalesHeader::create($validated);

            if (!$isCredit) {
                // Guardar los detalles de pago si existen
                if ($paymentDetails && is_array($paymentDetails) && count($paymentDetails) > 0) {
                    foreach ($paymentDetails as $payment) {
                        \App\Models\SalePaymentDetail::create([
                            'sale_id' => $salesHeader->id,
                            'payment_method_id' => $payment['payment_method_id'],
                            'payment_amount' => $payment['amount'],
                            'casher_id' => $validated['seller_id'], // Usar el vendedor como cajero
                            'bank_account_id' => $payment['bank_account_id'] ?? null,
                            'reference' => $payment['reference'] ?? null,
                            'actual_balance' => 0, // Saldo después de este pago
                            'is_active' => true,
                        ]);
                    }
                } else {
                    // ===== PAGO ÚNICO: También guardar en sale_payment_details para consistencia =====
                    if (isset($validated['payment_method_id']) && $validated['payment_method_id']) {
                        \App\Models\SalePaymentDetail::create([
                            'sale_id' => $salesHeader->id,
                            'payment_method_id' => $validated['payment_method_id'],
                            'payment_amount' => $validated['sale_total'],
                            'casher_id' => $validated['seller_id'],
                            'bank_account_id' => null,
                            'reference' => null,
                            'actual_balance' => 0,
                            'is_active' => true,
                        ]);
                    }
                }
            }

            DB::commit();
            return ApiResponse::success($salesHeader->load('paymentDetails.paymentMethod'), 'Venta creada con éxito', 201);

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

            $salesHeader = SalesHeader::findOrFail($id);

            $validated = $request->validated();

            // DEBUG: Ver qué está llegando
            Log::info('💳 SalesHeaderController.update() - Request completo:', $request->all());
            Log::info('💳 Validated payment_details:', ['payment_details' => $validated['payment_details'] ?? null]);

            // PROTECCIÓN: No permitir modificar document_internal_number desde el frontend
            // Solo el backend puede asignar este valor al generar el DTE
            unset($validated['document_internal_number']);

            // ===== SISTEMA DE PAGOS DIVIDIDOS (para actualización) =====
            $paymentDetails = $validated['payment_details'] ?? null;
            unset($validated['payment_details']); // Remover del array para no intentar insertarlo en sales_headers

            // Si hay pagos divididos, procesarlos
            if ($paymentDetails && is_array($paymentDetails) && count($paymentDetails) > 0) {
                Log::info('🔵 Iniciando procesamiento de pagos múltiples', ['count' => count($paymentDetails)]);

                // Validar que la suma de pagos coincida con el total de venta
                $totalPayments = array_reduce($paymentDetails, function($carry, $payment) {
                    return $carry + ($payment['amount'] ?? 0);
                }, 0);

                Log::info('🔵 Suma de pagos calculada', [
                    'totalPayments' => $totalPayments,
                    'sale_total' => $validated['sale_total'],
                    'difference' => abs($totalPayments - $validated['sale_total'])
                ]);

                if (abs($totalPayments - $validated['sale_total']) > 0.01) {
                    DB::rollBack();
                    Log::error('❌ Validación de suma falló');
                    return ApiResponse::error(
                        ['payment_mismatch' => "La suma de pagos ($" . number_format($totalPayments, 2) . ") no coincide con el total de venta ($" . number_format($validated['sale_total'], 2) . ")"],
                        'La suma de los métodos de pago no coincide con el total de la venta',
                        422
                    );
                }

                Log::info('✅ Validación de suma pasó');

                // Determinar el método principal para DTE
                $mainPayment = null;
                $cashPayment = null;

                foreach ($paymentDetails as $payment) {
                    $paymentMethod = \App\Models\PaymentMethod::find($payment['payment_method_id']);
                    if ($paymentMethod && $paymentMethod->code === '01') {
                        $cashPayment = $payment;
                        break;
                    }

                    if (!$mainPayment || $payment['amount'] > $mainPayment['amount']) {
                        $mainPayment = $payment;
                    }
                }

                $primaryPayment = $cashPayment ?? $mainPayment;
                $validated['payment_method_id'] = $primaryPayment['payment_method_id'];

                Log::info('🔵 Método principal seleccionado para DTE', [
                    'payment_method_id' => $primaryPayment['payment_method_id'],
                    'is_cash' => $cashPayment !== null
                ]);

                // ===== IMPORTANTE: Solo registrar pagos si NO es venta a crédito =====
                // operation_condition_id: 1=Contado, 2=A crédito, 3=Otro
                $isCredit = isset($validated['operation_condition_id']) && $validated['operation_condition_id'] == 2;

                if (!$isCredit) {
                    // Eliminar payment_details anteriores de esta venta
                    $deletedCount = \App\Models\SalePaymentDetail::where('sale_id', $salesHeader->id)->delete();
                    Log::info('🔵 Payment_details anteriores eliminados', ['count' => $deletedCount]);

                    // Guardar los nuevos detalles de pago
                    foreach ($paymentDetails as $index => $payment) {
                        Log::info("🔵 Guardando payment_detail #{$index}", $payment);
                        try {
                            $paymentDetail = \App\Models\SalePaymentDetail::create([
                                'sale_id' => $salesHeader->id,
                                'payment_method_id' => $payment['payment_method_id'],
                                'payment_amount' => $payment['amount'],
                                'casher_id' => $validated['seller_id'] ?? $salesHeader->seller_id,
                                'bank_account_id' => $payment['bank_account_id'] ?? null,
                                'reference' => $payment['reference'] ?? null,
                                'actual_balance' => 0,
                                'is_active' => true,
                            ]);
                            Log::info("✅ Payment_detail #{$index} guardado", ['id' => $paymentDetail->id]);
                        } catch (\Exception $e) {
                            Log::error("❌ Error guardando payment_detail #{$index}", ['error' => $e->getMessage()]);
                            throw $e;
                        }
                    }
                } else {
                    // Si es crédito, eliminar cualquier pago existente
                    $deletedCount = \App\Models\SalePaymentDetail::where('sale_id', $salesHeader->id)->delete();
                    Log::info('🔵 Venta a crédito - Payment_details eliminados', ['count' => $deletedCount]);
                }
            } else {
                // ===== PAGO ÚNICO: También guardar en sale_payment_details para consistencia =====
                // Esto facilita el cierre de caja y reportes al tener TODOS los pagos en un solo lugar
                // operation_condition_id: 1=Contado, 2=A crédito, 3=Otro
                $isCredit = isset($validated['operation_condition_id']) && $validated['operation_condition_id'] == 2;

                if (!$isCredit && isset($validated['payment_method_id']) && $validated['payment_method_id']) {
                    Log::info('🔵 Pago único detectado, guardando en payment_details', [
                        'payment_method_id' => $validated['payment_method_id']
                    ]);

                    // Eliminar payment_details anteriores de esta venta
                    $deletedCount = \App\Models\SalePaymentDetail::where('sale_id', $salesHeader->id)->delete();
                    Log::info('🔵 Payment_details anteriores eliminados', ['count' => $deletedCount]);

                    // Guardar el pago único
                    try {
                        $paymentDetail = \App\Models\SalePaymentDetail::create([
                            'sale_id' => $salesHeader->id,
                            'payment_method_id' => $validated['payment_method_id'],
                            'payment_amount' => $validated['sale_total'],
                            'casher_id' => $validated['seller_id'] ?? $salesHeader->seller_id,
                            'bank_account_id' => null,
                            'reference' => null,
                            'actual_balance' => 0,
                            'is_active' => true,
                        ]);
                        Log::info('✅ Pago único guardado en payment_details', ['id' => $paymentDetail->id]);
                    } catch (\Exception $e) {
                        Log::error('❌ Error guardando pago único', ['error' => $e->getMessage()]);
                        throw $e;
                    }
                } else if ($isCredit) {
                    // Si es crédito, eliminar cualquier pago existente
                    $deletedCount = \App\Models\SalePaymentDetail::where('sale_id', $salesHeader->id)->delete();
                    Log::info('🔵 Venta a crédito - Payment_details eliminados', ['count' => $deletedCount]);
                }
            }

            // DEBUG: Ver qué se va a guardar
            Log::info('📝 Datos a actualizar:', $validated);
            Log::info('📝 payment_method_id final:', ['payment_method_id' => $validated['payment_method_id'] ?? 'NO EXISTE']);
            Log::info('📝 operation_condition_id final:', ['operation_condition_id' => $validated['operation_condition_id'] ?? 'NO EXISTE']);

            $salesHeader->update($validated);

            // DEBUG: Ver qué se guardó
            $salesHeader->refresh();
            Log::info('✅ Después de update:', [
                'payment_method_id' => $salesHeader->payment_method_id,
                'operation_condition_id' => $salesHeader->operation_condition_id
            ]);

            DB::commit();
            return ApiResponse::success($salesHeader->load('paymentDetails.paymentMethod'), 'Venta actualizada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            DB::rollBack();
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
