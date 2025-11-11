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
            $query = SalePaymentDetail::with(['salesHeader', 'paymentMethod']);

            // Filtro por venta
            if ($request->has('sale_id')) {
                $query->where('id_sale', $request->input('sale_id'));
            }

            // Filtro por método de pago
            if ($request->has('payment_method_id')) {
                $query->where('id_payment_method', $request->input('payment_method_id'));
            }

            // Filtro por rango de fechas
            if ($request->has('date_from')) {
                $query->whereHas('salesHeader', function($q) use ($request) {
                    $q->whereDate('date', '>=', $request->input('date_from'));
                });
            }

            if ($request->has('date_to')) {
                $query->whereHas('salesHeader', function($q) use ($request) {
                    $q->whereDate('date', '<=', $request->input('date_to'));
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
            $payments = SalePaymentDetail::with(['paymentMethod'])
                ->where('id_sale', $saleId)
                ->get();

            return ApiResponse::success($payments, 'Detalles de pago por venta', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Crear múltiples detalles de pago
     */
    public function createMultiple(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_sale' => 'required|exists:sales_headers,id',
                'payments' => 'required|array|min:1',
                'payments.*.id_payment_method' => 'required|exists:payment_methods,id',
                'payments.*.amount' => 'required|numeric|min:0.01',
                'payments.*.reference' => 'nullable|string|max:255',
            ]);

            $payments = [];
            foreach ($request->payments as $paymentData) {
                $payment = SalePaymentDetail::create([
                    'id_sale' => $request->id_sale,
                    'id_payment_method' => $paymentData['id_payment_method'],
                    'amount' => $paymentData['amount'],
                    'reference' => $paymentData['reference'] ?? null,
                ]);
                $payments[] = $payment;
            }

            return ApiResponse::success($payments, 'Detalles de pago creados exitosamente', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }
    }

    /**
     * Validar que los pagos cubran el total de la venta
     */
    public function validatePayments(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_sale' => 'required|exists:sales_headers,id',
                'payments' => 'required|array|min:1',
                'payments.*.amount' => 'required|numeric|min:0.01',
            ]);

            // Obtener el total de la venta
            $sale = \App\Models\SalesHeader::findOrFail($request->id_sale);
            $saleTotal = $sale->total;

            // Calcular el total de los pagos
            $paymentsTotal = collect($request->payments)->sum('amount');

            $validation = [
                'sale_total' => $saleTotal,
                'payments_total' => $paymentsTotal,
                'difference' => $paymentsTotal - $saleTotal,
                'is_valid' => abs($paymentsTotal - $saleTotal) < 0.01, // Tolerancia de 1 centavo
                'is_complete' => $paymentsTotal >= $saleTotal,
                'change' => max(0, $paymentsTotal - $saleTotal),
            ];

            return ApiResponse::success($validation, 'Validación de pagos', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
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
}
