<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\SalesHeaderCollection;
use App\Http\Resources\Api\v1\SalesHeaderResource;
use App\Models\SalesHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesHeaderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $salesHeaders = SalesHeader::with(['customer:id,document_number,name,last_name,sales_type',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
            ])->paginate(10);
            return ApiResponse::success($salesHeaders, 'Venta recuperada con éxito',200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(SalesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $salesHeader = SalesHeader::create($request->validated());
          return ApiResponse::success($salesHeader, 'Venta creada con éxito',201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, SalesHeader $salesHeader): Response
    {
        return new SalesHeaderResource($salesHeader);
    }

    public function update(SalesHeaderUpdateRequest $request, SalesHeader $salesHeader): Response
    {
        $salesHeader->update($request->validated());

        return new SalesHeaderResource($salesHeader);
    }

    public function destroy(Request $request, SalesHeader $salesHeader): Response
    {
        $salesHeader->delete();

        return response()->noContent();
    }
}
