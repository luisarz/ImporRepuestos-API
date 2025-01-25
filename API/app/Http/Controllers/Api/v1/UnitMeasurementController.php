<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UnitMeasurementStoreRequest;
use App\Http\Requests\Api\v1\UnitMeasurementUpdateRequest;
use App\Http\Resources\Api\v1\UnitMeasurementCollection;
use App\Http\Resources\Api\v1\UnitMeasurementResource;
use App\Models\UnitMeasurement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnitMeasurementController extends Controller
{
    public function index(Request $request): Response
    {
        $unitMeasurements = UnitMeasurement::all();

        return new UnitMeasurementCollection($unitMeasurements);
    }

    public function store(UnitMeasurementStoreRequest $request): Response
    {
        $unitMeasurement = UnitMeasurement::create($request->validated());

        return new UnitMeasurementResource($unitMeasurement);
    }

    public function show(Request $request, UnitMeasurement $unitMeasurement): Response
    {
        return new UnitMeasurementResource($unitMeasurement);
    }

    public function update(UnitMeasurementUpdateRequest $request, UnitMeasurement $unitMeasurement): Response
    {
        $unitMeasurement->update($request->validated());

        return new UnitMeasurementResource($unitMeasurement);
    }

    public function destroy(Request $request, UnitMeasurement $unitMeasurement): Response
    {
        $unitMeasurement->delete();

        return response()->noContent();
    }
}
