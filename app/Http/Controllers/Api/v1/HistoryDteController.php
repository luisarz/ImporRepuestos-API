<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\HistoryDteStoreRequest;
use App\Http\Requests\Api\v1\HistoryDteUpdateRequest;
use App\Http\Resources\Api\v1\HistoryDteCollection;
use App\Http\Resources\Api\v1\HistoryDteResource;
use App\Models\HistoryDte;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HistoryDteController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $historyDtes = HistoryDte::paginate($perPage);
           return ApiResponse::success($historyDtes,'Detalle de ventas', 200);

        }catch (\Exception $e){
            return ApiResponse::success(null,$e->getMessage(),500);
        }
    }

    public function store(HistoryDteStoreRequest $request): Response
    {
        $historyDte = HistoryDte::create($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function show(Request $request, HistoryDte $historyDte): Response
    {
        return new HistoryDteResource($historyDte);
    }

    public function update(HistoryDteUpdateRequest $request, HistoryDte $historyDte): Response
    {
        $historyDte->update($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function destroy(Request $request, HistoryDte $historyDte): Response
    {
        $historyDte->delete();

        return response()->noContent();
    }
}
