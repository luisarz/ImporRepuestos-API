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
}
