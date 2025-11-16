<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchStoreRequest;
use App\Http\Requests\Api\v1\BatchUpdateRequest;
use App\Http\Requests\Api\v1\PurchaseItemStoreRequest;
use App\Http\Requests\Api\v1\PurchaseItemUpdateRequest;
use App\Http\Resources\Api\v1\PurchaseItemCollection;
use App\Http\Resources\Api\v1\PurchaseItemResource;
use App\Models\Batch;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseItemController extends Controller
{

    public function store(PurchaseItemStoreRequest $request,BatchStoreRequest $requestBatch): JsonResponse
    {

        try {
            DB::beginTransaction(); // Inicia la transacción
            $purchaseItem = (new PurchaseItem)->create($request->validated());
            $requestBatchData = $requestBatch->validated();
            $requestBatchData['purchase_item_id'] = $purchaseItem->id; // Aquí se asigna el ID

            // Crear Batch con el purchase_items_id obtenido
            $batch = (new Batch)->create($requestBatchData);

            $purchaseItems = PurchaseItem::with(['batch.inventory.product'])->where('purchase_id', $purchaseItem->purchase_id)->get();

            DB::commit(); // Confirma la transacción si todo salió bien

            return ApiResponse::success($purchaseItems, 'Ítems agregados y recuperados', 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre un error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show($id): JsonResponse
    {
        try {

            $purchaseItems = PurchaseItem::with(['batch.inventory.product'])->where('purchase_id', $id)->get();
            return ApiResponse::success($purchaseItems, 'Ítems agregados y recuperados', 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre un error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }


    }

    public function update(PurchaseItemUpdateRequest $request,BatchUpdateRequest $requestBatch,$id): JsonResponse
    {
        try {
            DB::beginTransaction(); // Inicia la transacción
            $purchaseItem= (new PurchaseItem)->findOrFail($id);
            $purchaseItem->update($request->validated());
            $batch= (new Batch)->where('purchase_item_id',$purchaseItem->id)->firstOrFail();
            $batch->update($requestBatch->validated());
            $purchaseItems = PurchaseItem::with(['batch.inventory.product'])->where('purchase_id', $purchaseItem->purchase_id)->get();
            DB::commit(); // Confirma la transacción si todo salió bien
            return ApiResponse::success($purchaseItems, 'Ítems agregados y recuperados', 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre un error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction(); // Inicia la transacción
            $purchaseItem= (new PurchaseItem)->findOrFail($id);
            $purchaseItem->delete();
//            $batch= (new Batch)->where('purchase_item_id',$purchaseItem->id)->firstOrFail();
//            $batch->delete();
            DB::commit(); // Confirma la transacción si todo salió bien
           return ApiResponse::success(null,'Item de compra eliminado de manera exitosa');
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Item no encontrado',404);
        }catch (\Exception $e){
            DB::rollBack();
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }


    }
}
