<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\WarehouseStoreRequest;
use App\Http\Requests\Api\v1\WarehouseUpdateRequest;
use App\Http\Resources\Api\v1\WarehouseCollection;
use App\Http\Resources\Api\v1\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WarehouseController extends Controller
{
    public function index(Request $request): Response
    {
        $warehouses = Warehouse::all();

        return new WarehouseCollection($warehouses);
    }

    public function store(WarehouseStoreRequest $request): Response
    {
        $warehouse = Warehouse::create($request->validated());

        return new WarehouseResource($warehouse);
    }

    public function show(Request $request, Warehouse $warehouse): Response
    {
        return new WarehouseResource($warehouse);
    }

    public function update(WarehouseUpdateRequest $request, Warehouse $warehouse): Response
    {
        $warehouse->update($request->validated());

        return new WarehouseResource($warehouse);
    }

    public function destroy(Request $request, Warehouse $warehouse): Response
    {
        $warehouse->delete();

        return response()->noContent();
    }
}
