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
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $warehouses = Warehouse::with('stablishmentType','district','economicActivity')->paginate(10);

        return response()->json($warehouses);
    }

    public function store(WarehouseStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $warehouse = Warehouse::create($request->validated());
            return response()->json([
                'data' => new WarehouseResource($warehouse),
                'status' => 'success',
                'message' => 'Warehouse created successfully',
            ], 201); // Código de respuesta 201: Created
        } catch (\Exception $e) {
            // Manejo de errores relacionados con la base de datos (clave foránea, duplicados, etc.)
            return response()->json([
                'data'=> $e->getMessage(),
                'status' => 'error',
                'message' => 'Warehouse not created',
            ]); // Código 422: Unprocessable Entity
        }
    }


    public function show(Request $request, Warehouse $warehouse): Response
    {
        return new WarehouseResource($warehouse);
    }

    public function update(WarehouseUpdateRequest $request, Warehouse $warehouse): WarehouseResource
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
