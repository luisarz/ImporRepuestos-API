<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\VehicleStoreRequest;
use App\Http\Requests\Api\v1\VehicleUpdateRequest;
use App\Http\Resources\Api\v1\VehicleCollection;
use App\Http\Resources\Api\v1\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = Vehicle::all();

        return new VehicleCollection($vehicles);
    }

    public function store(VehicleStoreRequest $request): Response
    {
        $vehicle = Vehicle::create($request->validated());

        return new VehicleResource($vehicle);
    }

    public function show(Request $request, Vehicle $vehicle): Response
    {
        return new VehicleResource($vehicle);
    }

    public function update(VehicleUpdateRequest $request, Vehicle $vehicle): Response
    {
        $vehicle->update($request->validated());

        return new VehicleResource($vehicle);
    }

    public function destroy(Request $request, Vehicle $vehicle): Response
    {
        $vehicle->delete();

        return response()->noContent();
    }
}
