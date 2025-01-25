<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SaleItemStoreRequest;
use App\Http\Requests\Api\v1\SaleItemUpdateRequest;
use App\Http\Resources\Api\v1\SaleItemCollection;
use App\Http\Resources\Api\v1\SaleItemResource;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleItemController extends Controller
{
    public function index(Request $request): Response
    {
        $saleItems = SaleItem::all();

        return new SaleItemCollection($saleItems);
    }

    public function store(SaleItemStoreRequest $request): Response
    {
        $saleItem = SaleItem::create($request->validated());

        return new SaleItemResource($saleItem);
    }

    public function show(Request $request, SaleItem $saleItem): Response
    {
        return new SaleItemResource($saleItem);
    }

    public function update(SaleItemUpdateRequest $request, SaleItem $saleItem): Response
    {
        $saleItem->update($request->validated());

        return new SaleItemResource($saleItem);
    }

    public function destroy(Request $request, SaleItem $saleItem): Response
    {
        $saleItem->delete();

        return response()->noContent();
    }
}
