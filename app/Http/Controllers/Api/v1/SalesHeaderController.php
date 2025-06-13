<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\SalesHeaderCollection;
use App\Http\Resources\Api\v1\SalesHeaderResource;
use App\Models\SalesHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesHeaderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $filtersJson = $request->input('filters') ?? '[]';
            $filters = json_decode($filtersJson, true) ?? [];


            $salesHeaders = SalesHeader::with([
                'customer:id,document_number,name,last_name,document_type_id',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
                'documentType',
                'paymentMethod',
                'saleCondition',

            ])
                ->whereHas('customer', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%");
                })
                ->paginate($perPage);
            $salesHeaders->getCollection()->transform(function ($sale) {
                $sale->formatted_date = $sale->sale_date->format('d/m/Y');
                $sale->total_sale_formatted = number_format($sale->sale_total, 2, '.', ',');
                return $sale;
            });
            return ApiResponse::success($salesHeaders, 'Venta recuperada con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(SalesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::create($request->validated());
            return ApiResponse::success($salesHeader, 'Venta creada con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show($id): JsonResponse
    {
        \Illuminate\Log\log($id);
        try {
//            $salesHeader = SalesHeader::with(['customer:id,document_number,name,last_name,sales_type',
//                'warehouse:id,name',
//                'seller:id,name,last_name,dui',
//                'items',
//                'items.inventory:id,code,name',
//            ])->findOrFail($id);
            $salesHeader = SalesHeader::findOrFail($id);
            return ApiResponse::success($salesHeader, 'Venta recuperada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(SalesHeaderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::findOrFail($id);
            $salesHeader->update($request->validated());
            return ApiResponse::success($salesHeader, 'Venta actualizada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::findOrFail($id);
            $salesHeader->delete();
            return ApiResponse::success(null, 'Venta eliminada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Venta no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
