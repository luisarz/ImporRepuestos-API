<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalePaymentDetailStoreRequest;
use App\Http\Requests\Api\v1\SalePaymentDetailUpdateRequest;
use App\Http\Resources\Api\v1\SalePaymentDetailCollection;
use App\Http\Resources\Api\v1\SalePaymentDetailResource;
use App\Models\SalePaymentDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalePaymentDetailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $query = SalePaymentDetail::with([
                'sale' => function($query) {
                    $query->select('id', 'document_internal_number', 'sale_total', 'sale_date', 'sale_status', 'payment_status', 'customer_id')
                        ->with('customer:id,name,last_name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ]);

            // Filtro por venta
            if ($request->has('sale_id')) {
                $query->where('sale_id', $request->input('sale_id'));
            }

            // Filtro por método de pago
            if ($request->has('payment_method') || $request->has('payment_method_id')) {
                $methodId = $request->input('payment_method') ?? $request->input('payment_method_id');
                $query->where('payment_method_id', $methodId);
            }

            // Filtro por rango de fechas
            if ($request->has('date_from')) {
                $query->whereHas('sale', function($q) use ($request) {
                    $q->whereDate('sale_date', '>=', $request->input('date_from'));
                });
            }

            if ($request->has('date_to')) {
                $query->whereHas('sale', function($q) use ($request) {
                    $q->whereDate('sale_date', '<=', $request->input('date_to'));
                });
            }

            // Búsqueda por texto
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhereHas('sale', function($sq) use ($search) {
                          $sq->where('document_internal_number', 'like', "%{$search}%")
                             ->orWhereHas('customer', function($cq) use ($search) {
                                 $cq->where('name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                             });
                      });
                });
            }

            // Verificar si se solicita agrupación por venta
            $grouped = $request->input('grouped', false);

            if ($grouped) {
                // Obtener todos los pagos (sin paginación para agrupar)
                $allPayments = $query->orderBy('created_at', 'desc')->get();

                // Agrupar por sale_id
                $groupedBySale = $allPayments->groupBy('sale_id')->map(function($payments, $saleId) {
                    $firstPayment = $payments->first();

                    return [
                        'sale_id' => $saleId,
                        'sale_header' => $firstPayment->sale,
                        'payments' => $payments->map(function($payment) {
                            return [
                                'id' => $payment->id,
                                'payment_method' => $payment->paymentMethod,
                                'payment_method_id' => $payment->payment_method_id,
                                'casher' => $payment->casher,
                                'casher_id' => $payment->casher_id,
                                'amount' => $payment->payment_amount,
                                'payment_amount' => $payment->payment_amount,
                                'reference' => $payment->reference,
                                'bank_account_id' => $payment->bank_account_id,
                                'actual_balance' => $payment->actual_balance,
                                'created_at' => $payment->created_at,
                                'updated_at' => $payment->updated_at,
                            ];
                        })->values(),
                        'payments_count' => $payments->count(),
                        'total_paid' => $payments->sum('payment_amount'),
                        'first_payment_date' => $payments->min('created_at'),
                        'last_payment_date' => $payments->max('created_at'),
                    ];
                })->values();

                // Paginar los grupos manualmente
                $currentPage = $request->input('page', 1);
                $pagedData = $groupedBySale->slice(($currentPage - 1) * $perPage, $perPage)->values();

                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $pagedData,
                    $groupedBySale->count(),
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return ApiResponse::success($paginator, 'Pagos agrupados por venta', 200);
            } else {
                // Respuesta normal sin agrupar
                $salePaymentDetails = $query->orderBy('created_at', 'desc')->paginate($perPage);

                // Transformar la respuesta para incluir sale_header en lugar de sale
                $salePaymentDetails->getCollection()->transform(function($payment) {
                    try {
                        $payment->sale_header = $payment->sale;
                        $payment->amount = $payment->payment_amount;
                        unset($payment->sale);
                        return $payment;
                    } catch (\Exception $e) {
                        \Log::error('Error transformando payment detail', [
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return $payment;
                    }
                });

                return ApiResponse::success($salePaymentDetails,'Detalles de pago recuperados', 200);
            }

        }catch (\Exception $exception){
            \Log::error('Error en SalePaymentDetailController@index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            return ApiResponse::error(null,$exception->getMessage(),500);
        }
    }

    /**
     * Obtener detalles de pago por venta
     */
    public function getBySale(Request $request, $saleId): JsonResponse
    {
        try {
            $payments = SalePaymentDetail::with(['paymentMethod', 'casher'])
                ->where('sale_id', $saleId)
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponse::success($payments, 'Detalles de pago por venta', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Registrar un pago/abono para una venta a crédito
     */
    public function registerPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sale_id' => 'required|exists:sales_headers,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'payment_amount' => 'required|numeric|min:0.01',
                'casher_id' => 'required|exists:employees,id',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'reference' => 'nullable|string|max:255',
            ]);

            $salesService = new \App\Services\SalesService();
            $payment = $salesService->registerPayment(
                $request->sale_id,
                $request->only(['payment_method_id', 'payment_amount', 'casher_id', 'bank_account_id', 'reference'])
            );

            return ApiResponse::success($payment, 'Pago registrado exitosamente', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Obtener historial de pagos de una venta
     */
    public function getPaymentHistory(Request $request, $saleId): JsonResponse
    {
        try {
            $salesService = new \App\Services\SalesService();
            $history = $salesService->getPaymentHistory($saleId);

            return ApiResponse::success($history, 'Historial de pagos', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    public function store(SalePaymentDetailStoreRequest $request): Response
    {
        $salePaymentDetail = SalePaymentDetail::create($request->validated());

        return new SalePaymentDetailResource($salePaymentDetail);
    }

    public function show(Request $request, SalePaymentDetail $salePaymentDetail): JsonResponse
    {
        try {
            $payment = SalePaymentDetail::with([
                'sale' => function($query) {
                    $query->with('customer:id,name,last_name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ])->findOrFail($salePaymentDetail->id);

            // Transformar sale a sale_header y payment_amount a amount
            $payment->sale_header = $payment->sale;
            $payment->amount = $payment->payment_amount;
            unset($payment->sale);

            return ApiResponse::success($payment, 'Detalle de pago recuperado', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    public function update(SalePaymentDetailUpdateRequest $request, SalePaymentDetail $salePaymentDetail): Response
    {
        $salePaymentDetail->update($request->validated());

        return new SalePaymentDetailResource($salePaymentDetail);
    }

    public function destroy(Request $request, SalePaymentDetail $salePaymentDetail): Response
    {
        $salePaymentDetail->delete();

        return response()->noContent();
    }

    /**
     * Obtener cuentas por cobrar (ventas pendientes de pago)
     */
    public function getAccountsReceivable(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['customer_id', 'warehouse_id', 'overdue_only']);
            $salesService = new \App\Services\SalesService();
            $accountsReceivable = $salesService->getAccountsReceivable($filters);

            return ApiResponse::success($accountsReceivable, 'Cuentas por cobrar', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Generar PDF de reporte de pagos por venta
     */
    public function printPaymentsPDF(Request $request, $saleId)
    {
        try {
            // Obtener los pagos de la venta
            $payments = SalePaymentDetail::with([
                'sale' => function($query) {
                    $query->with('customer:id,name,last_name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ])
            ->where('sale_id', $saleId)
            ->orderBy('created_at', 'asc')
            ->get();

            if ($payments->isEmpty()) {
                return response()->json(['error' => 'No se encontraron pagos para esta venta'], 404);
            }

            $saleHeader = $payments->first()->sale;
            $totalPaid = $payments->sum('payment_amount');
            $saleTotal = $saleHeader->sale_total;
            $balance = $saleTotal - $totalPaid;

            // Preparar datos para la vista
            $data = [
                'sale' => $saleHeader,
                'payments' => $payments,
                'totalPaid' => $totalPaid,
                'saleTotal' => $saleTotal,
                'balance' => $balance,
                'paymentsCount' => $payments->count(),
                'date' => now()->format('d/m/Y H:i:s')
            ];

            // Generar PDF con configuración de márgenes
            $pdf = \PDF::loadView('pdf.payment-report', $data);
            $pdf->setPaper('letter', 'portrait');

            // Configurar opciones de DomPDF
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            // Retornar el PDF para que se abra en el navegador (stream) en lugar de descargarlo
            return $pdf->stream('Pagos_Venta_' . $saleHeader->document_internal_number . '.pdf');

        } catch (\Exception $exception) {
            \Log::error('Error al generar PDF de pagos', [
                'sale_id' => $saleId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al generar el PDF: ' . $exception->getMessage()], 500);
        }
    }
}
