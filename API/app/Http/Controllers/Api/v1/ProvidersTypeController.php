<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProvidersTypeStoreRequest;
use App\Http\Requests\Api\v1\ProvidersTypeUpdateRequest;
use App\Http\Resources\Api\v1\ProvidersTypeCollection;
use App\Http\Resources\Api\v1\ProvidersTypeResource;
use App\Models\ProvidersType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProvidersTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $providersTypes = ProvidersType::all();

        return new ProvidersTypeCollection($providersTypes);
    }

    public function store(ProvidersTypeStoreRequest $request): Response
    {
        $providersType = ProvidersType::create($request->validated());

        return new ProvidersTypeResource($providersType);
    }

    public function show(Request $request, ProvidersType $providersType): Response
    {
        return new ProvidersTypeResource($providersType);
    }

    public function update(ProvidersTypeUpdateRequest $request, ProvidersType $providersType): Response
    {
        $providersType->update($request->validated());

        return new ProvidersTypeResource($providersType);
    }

    public function destroy(Request $request, ProvidersType $providersType): Response
    {
        $providersType->delete();

        return response()->noContent();
    }
}
