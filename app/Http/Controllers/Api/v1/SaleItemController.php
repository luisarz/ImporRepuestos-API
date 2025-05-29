<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SaleItemStoreRequest;
use App\Http\Requests\Api\v1\SaleItemUpdateRequest;
use App\Http\Resources\Api\v1\SaleItemCollection;
use App\Http\Resources\Api\v1\SaleItemResource;
use App\Models\SaleItem;
use App\Models\SalesHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleItemController extends Controller
{
    public function index($id, Request $request): JsonResponse
    {
        log("Sale ID " . $id);
        try {
            $perPage = $request->input('per_page', 100);

            $saleItems = SaleItem::with([
                'inventory:id,product_id',
                'inventory.product:id,original_code,description',
            ])->where('sale_id', $id)->paginate($perPage);
            return ApiResponse::success($saleItems, 'Items de venta recuperados con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Items de venta no encontrados', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(SaleItemStoreRequest $request): JsonResponse
    {
        try {
            $saleItem = SaleItem::create($request->validated());
            return ApiResponse::success($saleItem, 'Item de venta creado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function details($id, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);


            $saleItem = SaleItem::with([
                    'inventory',
                    'inventory.product',
                    'inventory.product.category',
                ]
            )->where('sale_id', $id)->paginate($perPage);
            return ApiResponse::success($saleItem, 'Venta recuperada con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            return ApiResponse::success($saleItem, 'Item de venta recuperado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(SaleItemUpdateRequest $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            $saleItem->update($request->validated());
            return ApiResponse::success($saleItem, 'Item de venta actualizado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            $saleItem->delete();
            return ApiResponse::success(null, 'Item de venta eliminado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
}
