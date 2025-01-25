<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\MunicipalityStoreRequest;
use App\Http\Requests\Api\v1\MunicipalityUpdateRequest;
use App\Http\Resources\Api\v1\MunicipalityCollection;
use App\Http\Resources\Api\v1\MunicipalityResource;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MunicipalityController extends Controller
{
    public function index(Request $request): MunicipalityCollection
    {
        $municipalities = Municipality::with('department')->paginate(10);
        return new MunicipalityCollection($municipalities);
    }

    public function store(MunicipalityStoreRequest $request): MunicipalityResource
    {
        $municipality = (new \App\Models\Municipality)->create($request->validated());

        return new MunicipalityResource($municipality);
    }

    public function show(Request $request, Municipality $municipality): MunicipalityResource
    {
        return new MunicipalityResource($municipality);
    }

    public function update(MunicipalityUpdateRequest $request, Municipality $municipality): MunicipalityResource
    {
        $municipality->update($request->validated());
        return new MunicipalityResource($municipality);
    }

    public function destroy(Request $request, Municipality $municipality): Response
    {
        $municipality->delete();

        return response()->noContent();
    }
}
