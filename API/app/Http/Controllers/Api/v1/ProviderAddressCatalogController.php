<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\ProviderAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\ProviderAddressCatalogCollection;
use App\Http\Resources\Api\v1\ProviderAddressCatalogResource;
use App\Models\ProviderAddressCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderAddressCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $providerAddressCatalogs = ProviderAddressCatalog::all();

        return new ProviderAddressCatalogCollection($providerAddressCatalogs);
    }

    public function store(ProviderAddressCatalogStoreRequest $request): Response
    {
        $providerAddressCatalog = ProviderAddressCatalog::create($request->validated());

        return new ProviderAddressCatalogResource($providerAddressCatalog);
    }

    public function show(Request $request, ProviderAddressCatalog $providerAddressCatalog): Response
    {
        return new ProviderAddressCatalogResource($providerAddressCatalog);
    }

    public function update(ProviderAddressCatalogUpdateRequest $request, ProviderAddressCatalog $providerAddressCatalog): Response
    {
        $providerAddressCatalog->update($request->validated());

        return new ProviderAddressCatalogResource($providerAddressCatalog);
    }

    public function destroy(Request $request, ProviderAddressCatalog $providerAddressCatalog): Response
    {
        $providerAddressCatalog->delete();

        return response()->noContent();
    }
}
