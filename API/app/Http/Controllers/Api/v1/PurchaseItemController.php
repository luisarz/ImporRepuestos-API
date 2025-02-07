<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchStoreRequest;
use App\Http\Requests\Api\v1\PurchaseItemStoreRequest;
use App\Http\Requests\Api\v1\PurchaseItemUpdateRequest;
use App\Http\Resources\Api\v1\PurchaseItemCollection;
use App\Http\Resources\Api\v1\PurchaseItemResource;
use App\Models\Batch;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseItemController extends Controller
{

    public function store(PurchaseItemStoreRequest $request,BatchStoreRequest $requestBatch): \Illuminate\Http\JsonResponse
    {

        try {
            DB::beginTransaction(); // Inicia la transacción
            $purchaseItem = (new PurchaseItem)->create($request->validated());
            $batch = (new Batch)->create($requestBatch->validated());
            $purchaseItems = PurchaseItem::where('purchase_id', $purchaseItem->purchase_id)->get();

            DB::commit(); // Confirma la transacción si todo salió bien

            return ApiResponse::success($purchaseItems, 'Ítems agregados y recuperados', 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre un error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

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
