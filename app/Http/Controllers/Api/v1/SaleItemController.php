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
            //Actualizar el total de la venta
            $sale = SalesHeader::findOrFail($saleItem->sale_id);
            $sale->sale_total += $saleItem->total;
            $sale->save();
            $saleItem['formatted_price'] = '$' . number_format($saleItem->price, 2);

            return ApiResponse::success($saleItem, 'Item de venta creado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function details($id, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 100);
            $saleItem = SaleItem::with([
                'inventory',
                'inventory.product',
                'inventory.product.category',
            ])
                ->where('sale_id', $id)
                ->paginate($perPage)
                ->through(function ($item) {
                    $item->formatted_price = '$' . number_format($item->price, 2);
                    $item->formatted_total = '$' . number_format($item->total, 2);
                    return $item;
                });

            return ApiResponse::success($saleItem, 'Venta recuperada con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
    public function totalSale($id): JsonResponse
    {
        try {
            $total = SaleItem::where('sale_id', $id)->sum('total');
            $neto=number_format($total/1.13,2);
            $iva=number_format(  $neto*0.13,2);
            $saleItem = [
                'total' =>number_format($total,2),
                'neto' => $neto,
                'iva' => $iva,
            ];



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
