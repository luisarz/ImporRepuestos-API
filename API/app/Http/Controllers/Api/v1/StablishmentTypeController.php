<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StablishmentTypeStoreRequest;
use App\Http\Requests\Api\v1\StablishmentTypeUpdateRequest;
use App\Http\Resources\Api\v1\StablishmentTypeCollection;
use App\Http\Resources\Api\v1\StablishmentTypeResource;
use App\Models\StablishmentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StablishmentTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $stablishmentTypes = StablishmentType::all();

        return new StablishmentTypeCollection($stablishmentTypes);
    }

    public function store(StablishmentTypeStoreRequest $request): Response
    {
        $stablishmentType = StablishmentType::create($request->validated());

        return new StablishmentTypeResource($stablishmentType);
    }

    public function show(Request $request, StablishmentType $stablishmentType): Response
    {
        return new StablishmentTypeResource($stablishmentType);
    }

    public function update(StablishmentTypeUpdateRequest $request, StablishmentType $stablishmentType): Response
    {
        $stablishmentType->update($request->validated());

        return new StablishmentTypeResource($stablishmentType);
    }

    public function destroy(Request $request, StablishmentType $stablishmentType): Response
    {
        $stablishmentType->delete();

        return response()->noContent();
    }
}
