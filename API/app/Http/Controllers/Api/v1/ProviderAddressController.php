<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderAddressStoreRequest;
use App\Http\Requests\Api\v1\ProviderAddressUpdateRequest;
use App\Http\Resources\Api\v1\ProviderAddressCollection;
use App\Http\Resources\Api\v1\ProviderAddressResource;
use App\Models\ProviderAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderAddressController extends Controller
{
    public function index(Request $request): Response
    {
        $providerAddresses = ProviderAddress::all();

        return new ProviderAddressCollection($providerAddresses);
    }

    public function store(ProviderAddressStoreRequest $request): Response
    {
        $providerAddress = ProviderAddress::create($request->validated());

        return new ProviderAddressResource($providerAddress);
    }

    public function show(Request $request, ProviderAddress $providerAddress): Response
    {
        return new ProviderAddressResource($providerAddress);
    }

    public function update(ProviderAddressUpdateRequest $request, ProviderAddress $providerAddress): Response
    {
        $providerAddress->update($request->validated());

        return new ProviderAddressResource($providerAddress);
    }

    public function destroy(Request $request, ProviderAddress $providerAddress): Response
    {
        $providerAddress->delete();

        return response()->noContent();
    }
}
