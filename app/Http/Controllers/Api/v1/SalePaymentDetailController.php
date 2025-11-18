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
            $query = SalePaymentDetail::with(['sale', 'paymentMethod', 'casher']);

            // Filtro por venta
            if ($request->has('sale_id')) {
                $query->where('sale_id', $request->input('sale_id'));
            }

            // Filtro por método de pago
            if ($request->has('payment_method_id')) {
                $query->where('payment_method_id', $request->input('payment_method_id'));
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

            $salePaymentDetails = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return ApiResponse::success($salePaymentDetails,'Detalles de pago recuperados', 200);

        }catch (\Exception $exception){
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

    public function show(Request $request, SalePaymentDetail $salePaymentDetail): Response
    {
        return new SalePaymentDetailResource($salePaymentDetail);
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
}
