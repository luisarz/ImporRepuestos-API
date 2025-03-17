<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoriesBatchStoreRequest;
use App\Http\Requests\Api\v1\InventoriesBatchUpdateRequest;
use App\Http\Resources\Api\v1\InventoriesBatchCollection;
use App\Http\Resources\Api\v1\InventoriesBatchResource;
use App\Models\InventoriesBatch;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoriesBatchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $inventoriesBatches = InventoriesBatch::with(['inventory', 'batch'])->paginate($perPage);
            return ApiResponse::success($inventoriesBatches, 'Movimiento de lotes e inventario recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(InventoriesBatchStoreRequest $request): JsonResponse
    {
        try {
            $inventoriesBatch = (new InventoriesBatch)->create($request->validated());
            return ApiResponse::success($inventoriesBatch, 'Movimiento de inventario por lote creado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $inventoriesBatch = (new InventoriesBatch)->findOrFail($id);
            return ApiResponse::success($inventoriesBatch, 'Movimiento de inventario por lote recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Movimiento no encontrad', 404);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }
    }

//    public function update(InventoriesBatchUpdateRequest $request, InventoriesBatch $inventoriesBatch): Response
//    {
//        $inventoriesBatch->update($request->validated());
//
//        return new InventoriesBatchResource($inventoriesBatch);
//    }

//    public function destroy(Request $request, InventoriesBatch $inventoriesBatch): Response
//    {
//        $inventoriesBatch->delete();
//
//        return response()->noContent();
//    }
}
