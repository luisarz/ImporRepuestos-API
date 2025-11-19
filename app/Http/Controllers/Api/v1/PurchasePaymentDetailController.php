<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\PurchasePaymentDetail;
use App\Services\PurchasesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PurchasePaymentDetailController extends Controller
{
    /**
     * Listar todos los pagos de compras (paginado y con filtros)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $query = PurchasePaymentDetail::with([
                'purchase' => function($query) {
                    $query->select('id', 'purchase_number', 'total_purchase', 'purchase_date', 'status_purchase', 'payment_status', 'provider_id')
                        ->with('provider:id,name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ]);

            // Filtro por compra
            if ($request->has('purchase_id')) {
                $query->where('purchase_id', $request->input('purchase_id'));
            }

            // Filtro por método de pago
            if ($request->has('payment_method') || $request->has('payment_method_id')) {
                $methodId = $request->input('payment_method') ?? $request->input('payment_method_id');
                $query->where('payment_method_id', $methodId);
            }

            // Filtro por proveedor
            if ($request->has('provider_id')) {
                $query->whereHas('purchase', function($q) use ($request) {
                    $q->where('provider_id', $request->input('provider_id'));
                });
            }

            // Filtro por rango de fechas
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Búsqueda por texto
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhereHas('purchase', function($sq) use ($search) {
                          $sq->where('purchase_number', 'like', "%{$search}%")
                             ->orWhereHas('provider', function($pq) use ($search) {
                                 $pq->where('name', 'like', "%{$search}%");
                             });
                      });
                });
            }

            $purchasePaymentDetails = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Transformar la respuesta
            $purchasePaymentDetails->getCollection()->transform(function($payment) {
                $payment->purchase_header = $payment->purchase;
                $payment->amount = $payment->payment_amount;
                unset($payment->purchase);
                return $payment;
            });

            return ApiResponse::success($purchasePaymentDetails,'Detalles de pago de compras recuperados', 200);

        } catch (\Exception $exception) {
            \Log::error('Error en PurchasePaymentDetailController@index', [
                'error' => $exception->getMessage()
            ]);
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Registrar un pago/abono para una compra a crédito
     */
    public function registerPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'purchase_id' => 'required|exists:purchases_headers,id',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'payment_amount' => 'required|numeric|min:0.01',
                'casher_id' => 'required|exists:employees,id',
                'bank_account_id' => 'nullable',
                'reference' => 'nullable|string|max:255',
            ]);

            $purchasesService = new PurchasesService();
            $payment = $purchasesService->registerPayment(
                $request->purchase_id,
                $request->only(['payment_method_id', 'payment_amount', 'casher_id', 'bank_account_id', 'reference'])
            );

            return ApiResponse::success($payment, 'Pago registrado exitosamente', 201);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Obtener historial de pagos de una compra
     */
    public function getPaymentHistory(Request $request, $purchaseId): JsonResponse
    {
        try {
            $purchasesService = new PurchasesService();
            $history = $purchasesService->getPaymentHistory($purchaseId);

            return ApiResponse::success($history, 'Historial de pagos', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Obtener cuentas por pagar
     */
    public function getAccountsPayable(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['provider_id', 'warehouse_id', 'overdue_only']);
            $purchasesService = new PurchasesService();
            $accountsPayable = $purchasesService->getAccountsPayable($filters);

            return ApiResponse::success($accountsPayable, 'Cuentas por pagar', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de cuentas por pagar
     */
    public function getAccountsPayableStats(Request $request): JsonResponse
    {
        try {
            $purchasesService = new PurchasesService();
            $stats = $purchasesService->getAccountsPayableStats();

            return ApiResponse::success($stats, 'Estadísticas de cuentas por pagar', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Generar PDF de reporte de pagos por compra
     */
    public function printPaymentsPDF(Request $request, $purchaseId)
    {
        try {
            $payments = PurchasePaymentDetail::with([
                'purchase' => function($query) {
                    $query->with('provider:id,comercial_name,legal_name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ])
            ->where('purchase_id', $purchaseId)
            ->orderBy('created_at', 'asc')
            ->get();

            if ($payments->isEmpty()) {
                return response()->json(['error' => 'No se encontraron pagos para esta compra'], 404);
            }

            $purchase = $payments->first()->purchase;
            $totalPaid = $payments->sum('payment_amount');
            $purchaseTotal = $purchase->total_purchase;
            $balance = $purchaseTotal - $totalPaid;

            $data = [
                'purchase' => $purchase,
                'payments' => $payments,
                'totalPaid' => $totalPaid,
                'purchaseTotal' => $purchaseTotal,
                'balance' => $balance,
                'paymentsCount' => $payments->count(),
                'date' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = \PDF::loadView('pdf.purchase-payment-report', $data);
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            return $pdf->stream('Pagos_Compra_' . $purchase->purchase_number . '.pdf');

        } catch (\Exception $exception) {
            \Log::error('Error al generar PDF de pagos de compra', [
                'purchase_id' => $purchaseId,
                'error' => $exception->getMessage()
            ]);
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }

    /**
     * Generar PDF de un pago individual
     */
    public function printSinglePaymentPDF(Request $request, $paymentId)
    {
        try {
            $payment = PurchasePaymentDetail::with([
                'purchase' => function($query) {
                    $query->with('provider:id,comercial_name,legal_name');
                },
                'paymentMethod:id,name,code',
                'casher:id,name,last_name'
            ])->findOrFail($paymentId);

            $purchase = $payment->purchase;

            // Calcular el saldo anterior
            $previousPayments = PurchasePaymentDetail::where('purchase_id', $purchase->id)
                ->where('id', '<', $payment->id)
                ->sum('payment_amount');

            $previousBalance = $purchase->total_purchase - $previousPayments;

            $data = [
                'payment' => $payment,
                'purchase' => $purchase,
                'previousBalance' => $previousBalance,
                'date' => now()->format('d/m/Y H:i:s')
            ];

            $pdf = \PDF::loadView('pdf.purchase-single-payment', $data);
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            return $pdf->stream('Pago_' . $payment->id . '_Compra_' . $purchase->purchase_number . '.pdf');

        } catch (\Exception $exception) {
            \Log::error('Error al generar PDF de pago individual', [
                'payment_id' => $paymentId,
                'error' => $exception->getMessage()
            ]);
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }
}
