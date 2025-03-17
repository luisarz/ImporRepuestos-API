<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\QuotePurchaseStoreRequest;
use App\Http\Requests\Api\v1\QuotePurchaseUpdateRequest;
use App\Http\Resources\Api\v1\QuotePurchaseCollection;
use App\Http\Resources\Api\v1\QuotePurchaseResource;
use App\Models\QuotePurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotePurchaseController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $quotePurchases = QuotePurchase::paginate($perPage);
            return ApiResponse::success($quotePurchases,'Detalle de ventas', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null,'Ocurrió un error',500)
        }
    }

    public function store(QuotePurchaseStoreRequest $request): Response
    {
        $quotePurchase = QuotePurchase::create($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function show(Request $request, QuotePurchase $quotePurchase): Response
    {
        return new QuotePurchaseResource($quotePurchase);
    }

    public function update(QuotePurchaseUpdateRequest $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->update($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function destroy(Request $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->delete();

        return response()->noContent();
    }
}
