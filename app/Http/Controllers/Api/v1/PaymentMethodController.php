<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PaymentMethodStoreRequest;
use App\Http\Requests\Api\v1\PaymentMethodUpdateRequest;
use App\Http\Resources\Api\v1\PaymentMethodCollection;
use App\Http\Resources\Api\v1\PaymentMethodResource;
use App\Models\OperationCondition;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $paymentMethods = PaymentMethod::where('name', 'like', "%$search%")->paginate($perPage);
            return ApiResponse::success($paymentMethods, 'Operation conditions retrieved successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while retrieving the operation conditions', 500);
        }
    }

    public function store(PaymentMethodStoreRequest $request): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::create($request->validated());
            return ApiResponse::success($paymentMethod, 'Operation condition created successfully', 201);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while creating the operation condition', 500);
        }

    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::findorfail($id);
            return ApiResponse::success($paymentMethod, 'Operation condition retrieved successfully', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Operation condition not found', 404);
        }
    }

    public function update(PaymentMethodUpdateRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $paymentMethod->update($request->validated());
            return ApiResponse::success($paymentMethod, 'Operation condition updated successfully', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Operation condition not found', 404);
        }

    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): Response
    {
        $paymentMethod->delete();

        return response()->noContent();
    }

    public function stats(): JsonResponse
    {
        try {
            $total = PaymentMethod::count();
            $active = PaymentMethod::where('is_active', 1)->count();
            $inactive = PaymentMethod::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $paymentMethods = PaymentMethod::whereIn('id', $ids)->get();
            return ApiResponse::success($paymentMethods, 'Métodos de pago recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PaymentMethod::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Métodos de pago activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PaymentMethod::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Métodos de pago desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PaymentMethod::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Métodos de pago eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
