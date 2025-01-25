<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\VehicleModelStoreRequest;
use App\Http\Requests\Api\v1\VehicleModelUpdateRequest;
use App\Http\Resources\Api\v1\VehicleModelCollection;
use App\Http\Resources\Api\v1\VehicleModelResource;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleModelController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicleModels = VehicleModel::all();

        return new VehicleModelCollection($vehicleModels);
    }

    public function store(VehicleModelStoreRequest $request): Response
    {
        $vehicleModel = VehicleModel::create($request->validated());

        return new VehicleModelResource($vehicleModel);
    }

    public function show(Request $request, VehicleModel $vehicleModel): Response
    {
        return new VehicleModelResource($vehicleModel);
    }

    public function update(VehicleModelUpdateRequest $request, VehicleModel $vehicleModel): Response
    {
        $vehicleModel->update($request->validated());

        return new VehicleModelResource($vehicleModel);
    }

    public function destroy(Request $request, VehicleModel $vehicleModel): Response
    {
        $vehicleModel->delete();

        return response()->noContent();
    }
}
