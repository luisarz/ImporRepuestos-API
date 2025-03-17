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
    public function index(Request $request,$id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $saleItems = SaleItem::with([
                'inventory:id,product_id',
                'inventory.product:id,original_code,description',
            ])->where('sale_id', $id)->paginate($perPage);
            return ApiResponse::success($saleItems, 'Items de venta recuperados con éxito', 200);
        }catch (ModelNotFoundException $exception){
            return ApiResponse::error($exception->getMessage(), 'Items de venta no encontrados', 404);
        }
        catch (\Exception $e) {
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
    public function details(Request $request,$id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $salesHeaders = SalesHeader::with(['customer:id,document_number,name,last_name,sales_type',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
                'items:id,sale_id,inventory_id,batch_id,saled,quantity,price,discount,total,is_saled,is_active',
                'items.inventory:id,product_id',
                'items.inventory.product:id,original_code,description',
            ])->where('id', $id)
                ->paginate($perPage);
            return ApiResponse::success($salesHeaders, 'Venta recuperada con éxito', 200);
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
        }catch (ModelNotFoundException $exception){
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        }
        catch (\Exception $e) {
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
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
}
