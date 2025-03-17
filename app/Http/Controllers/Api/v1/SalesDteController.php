<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesDteStoreRequest;
use App\Http\Requests\Api\v1\SalesDteUpdateRequest;
use App\Http\Resources\Api\v1\SalesDteCollection;
use App\Http\Resources\Api\v1\SalesDteResource;
use App\Models\SalesDte;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesDteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $salesDtes = SalesDte::paginate($perPage);
            return ApiResponse::success($salesDtes,'Detalle de ventas', 200);

        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'Ocurrió un error', 500);
        }

    }

    public function store(SalesDteStoreRequest $request): Response
    {
        $salesDte = SalesDte::create($request->validated());

        return new SalesDteResource($salesDte);
    }

    public function show(Request $request, SalesDte $salesDte): Response
    {
        return new SalesDteResource($salesDte);
    }

    public function update(SalesDteUpdateRequest $request, SalesDte $salesDte): Response
    {
        $salesDte->update($request->validated());

        return new SalesDteResource($salesDte);
    }

    public function destroy(Request $request, SalesDte $salesDte): Response
    {
        $salesDte->delete();

        return response()->noContent();
    }
}
