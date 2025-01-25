<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PlateTypeStoreRequest;
use App\Http\Requests\Api\v1\PlateTypeUpdateRequest;
use App\Http\Resources\Api\v1\PlateTypeCollection;
use App\Http\Resources\Api\v1\PlateTypeResource;
use App\Models\PlateType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlateTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $plateTypes = PlateType::all();

        return new PlateTypeCollection($plateTypes);
    }

    public function store(PlateTypeStoreRequest $request): Response
    {
        $plateType = PlateType::create($request->validated());

        return new PlateTypeResource($plateType);
    }

    public function show(Request $request, PlateType $plateType): Response
    {
        return new PlateTypeResource($plateType);
    }

    public function update(PlateTypeUpdateRequest $request, PlateType $plateType): Response
    {
        $plateType->update($request->validated());

        return new PlateTypeResource($plateType);
    }

    public function destroy(Request $request, PlateType $plateType): Response
    {
        $plateType->delete();

        return response()->noContent();
    }
}
