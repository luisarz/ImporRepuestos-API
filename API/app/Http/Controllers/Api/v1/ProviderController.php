<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderStoreRequest;
use App\Http\Requests\Api\v1\ProviderUpdateRequest;
use App\Http\Resources\Api\v1\ProviderCollection;
use App\Http\Resources\Api\v1\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderController extends Controller
{
    public function index(Request $request): Response
    {
        $providers = Provider::all();

        return new ProviderCollection($providers);
    }

    public function store(ProviderStoreRequest $request): Response
    {
        $provider = Provider::create($request->validated());

        return new ProviderResource($provider);
    }

    public function show(Request $request, Provider $provider): Response
    {
        return new ProviderResource($provider);
    }

    public function update(ProviderUpdateRequest $request, Provider $provider): Response
    {
        $provider->update($request->validated());

        return new ProviderResource($provider);
    }

    public function destroy(Request $request, Provider $provider): Response
    {
        $provider->delete();

        return response()->noContent();
    }
}
