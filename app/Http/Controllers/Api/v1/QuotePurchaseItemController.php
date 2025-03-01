<?php

namespace App\Http\Controllers\Api\v1;

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
    public function index(Request $request): Response
    {
        $quotePurchaseItems = QuotePurchaseItem::all();

        return new QuotePurchaseItemCollection($quotePurchaseItems);
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
