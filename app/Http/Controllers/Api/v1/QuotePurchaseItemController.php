<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\QuotePurchaseItemStoreRequest;
use App\Http\Requests\Api\v1\QuotePurchaseItemUpdateRequest;
use App\Http\Resources\Api\v1\QuotePurchaseItemCollection;
use App\Http\Resources\Api\v1\QuotePurchaseItemResource;
use App\Models\QuotePurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotePurchaseItemController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $quotePurchaseItems = QuotePurchaseItem::all();
            return ApiResponse::success($quotePurchaseItems, 'Detalle de ventas', 200);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }

    }

    public function store(QuotePurchaseItemStoreRequest $request): Response
    {
        $quotePurchaseItem = QuotePurchaseItem::create($request->validated());

        return new QuotePurchaseItemResource($quotePurchaseItem);
    }

    public function show(Request $request, QuotePurchaseItem $quotePurchaseItem): Response
    {
        return new QuotePurchaseItemResource($quotePurchaseItem);
    }

    public function update(QuotePurchaseItemUpdateRequest $request, QuotePurchaseItem $quotePurchaseItem): Response
    {
        $quotePurchaseItem->update($request->validated());

        return new QuotePurchaseItemResource($quotePurchaseItem);
    }

    public function destroy(Request $request, QuotePurchaseItem $quotePurchaseItem): Response
    {
        $quotePurchaseItem->delete();

        return response()->noContent();
    }
}
