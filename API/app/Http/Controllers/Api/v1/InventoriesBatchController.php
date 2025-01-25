<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoriesBatchStoreRequest;
use App\Http\Requests\Api\v1\InventoriesBatchUpdateRequest;
use App\Http\Resources\Api\v1\InventoriesBatchCollection;
use App\Http\Resources\Api\v1\InventoriesBatchResource;
use App\Models\InventoriesBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoriesBatchController extends Controller
{
    public function index(Request $request): Response
    {
        $inventoriesBatches = InventoriesBatch::all();

        return new InventoriesBatchCollection($inventoriesBatches);
    }

    public function store(InventoriesBatchStoreRequest $request): Response
    {
        $inventoriesBatch = InventoriesBatch::create($request->validated());

        return new InventoriesBatchResource($inventoriesBatch);
    }

    public function show(Request $request, InventoriesBatch $inventoriesBatch): Response
    {
        return new InventoriesBatchResource($inventoriesBatch);
    }

    public function update(InventoriesBatchUpdateRequest $request, InventoriesBatch $inventoriesBatch): Response
    {
        $inventoriesBatch->update($request->validated());

        return new InventoriesBatchResource($inventoriesBatch);
    }

    public function destroy(Request $request, InventoriesBatch $inventoriesBatch): Response
    {
        $inventoriesBatch->delete();

        return response()->noContent();
    }
}
