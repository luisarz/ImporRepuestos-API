<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PurchaseItemStoreRequest;
use App\Http\Requests\Api\v1\PurchaseItemUpdateRequest;
use App\Http\Resources\Api\v1\PurchaseItemCollection;
use App\Http\Resources\Api\v1\PurchaseItemResource;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchaseItemController extends Controller
{
    public function index(Request $request): Response
    {
        $purchaseItems = PurchaseItem::all();

        return new PurchaseItemCollection($purchaseItems);
    }

    public function store(PurchaseItemStoreRequest $request): Response
    {
        $purchaseItem = PurchaseItem::create($request->validated());

        return new PurchaseItemResource($purchaseItem);
    }

    public function show(Request $request, PurchaseItem $purchaseItem): Response
    {
        return new PurchaseItemResource($purchaseItem);
    }

    public function update(PurchaseItemUpdateRequest $request, PurchaseItem $purchaseItem): Response
    {
        $purchaseItem->update($request->validated());

        return new PurchaseItemResource($purchaseItem);
    }

    public function destroy(Request $request, PurchaseItem $purchaseItem): Response
    {
        $purchaseItem->delete();

        return response()->noContent();
    }
}
