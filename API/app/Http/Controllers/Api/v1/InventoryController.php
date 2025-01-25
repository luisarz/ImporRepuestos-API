<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InventoryStoreRequest;
use App\Http\Requests\Api\v1\InventoryUpdateRequest;
use App\Http\Resources\Api\v1\InventoryCollection;
use App\Http\Resources\Api\v1\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $inventories = Inventory::all();

        return new InventoryCollection($inventories);
    }

    public function store(InventoryStoreRequest $request): Response
    {
        $inventory = Inventory::create($request->validated());

        return new InventoryResource($inventory);
    }

    public function show(Request $request, Inventory $inventory): Response
    {
        return new InventoryResource($inventory);
    }

    public function update(InventoryUpdateRequest $request, Inventory $inventory): Response
    {
        $inventory->update($request->validated());

        return new InventoryResource($inventory);
    }

    public function destroy(Request $request, Inventory $inventory): Response
    {
        $inventory->delete();

        return response()->noContent();
    }
}
