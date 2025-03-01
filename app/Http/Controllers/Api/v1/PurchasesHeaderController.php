<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PurchasesHeaderStoreRequest;
use App\Http\Requests\Api\v1\PurchasesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\PurchasesHeaderCollection;
use App\Http\Resources\Api\v1\PurchasesHeaderResource;
use App\Models\PurchasesHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchasesHeaderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $purchasesHeaders = PurchasesHeader::with(
                [
                    'warehouse:id,name,address,phone,email',
                    'quotePurchase',
                    'provider:id,comercial_name,document_number,payment_type_id'
                ]
            )->paginate(10);
            return ApiResponse::success($purchasesHeaders, 'Compras cargadas', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(PurchasesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $purchasesHeader = (new PurchasesHeader)->create($request->validated());
            return ApiResponse::success($purchasesHeader, 'Compra Iniciada exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new PurchasesHeader)->findOrFail($id);
            return ApiResponse::success($purchasesHeader, 'Cabecera de compra recuperada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'La cabecera de compra no existe');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar el header de la compra');
        }
    }

    public function update(PurchasesHeaderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new \App\Models\PurchasesHeader)->findOrFail($id);
            $purchasesHeader->update($request->validated());
            return ApiResponse::success($purchasesHeader, 'Header de compra Modificada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Cabecera de compra no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al modificar el header de compra', 500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $purchasesHeader = (new \App\Models\PurchasesHeader)->findOrFail($id);
            $purchasesHeader->delete();
            return ApiResponse::success($purchasesHeader, 'Header de compra Modificada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Cabecera de compra no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al modificar el header de compra', 500);
        }

    }
}
