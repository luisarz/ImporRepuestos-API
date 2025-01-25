<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\FuelTypeStoreRequest;
use App\Http\Requests\Api\v1\FuelTypeUpdateRequest;
use App\Http\Resources\Api\v1\FuelTypeCollection;
use App\Http\Resources\Api\v1\FuelTypeResource;
use App\Models\FuelType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FuelTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $fuelTypes = FuelType::all();

        return new FuelTypeCollection($fuelTypes);
    }

    public function store(FuelTypeStoreRequest $request): Response
    {
        $fuelType = FuelType::create($request->validated());

        return new FuelTypeResource($fuelType);
    }

    public function show(Request $request, FuelType $fuelType): Response
    {
        return new FuelTypeResource($fuelType);
    }

    public function update(FuelTypeUpdateRequest $request, FuelType $fuelType): Response
    {
        $fuelType->update($request->validated());

        return new FuelTypeResource($fuelType);
    }

    public function destroy(Request $request, FuelType $fuelType): Response
    {
        $fuelType->delete();

        return response()->noContent();
    }
}
