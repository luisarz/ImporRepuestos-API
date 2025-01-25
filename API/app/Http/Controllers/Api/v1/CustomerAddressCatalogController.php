<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\CustomerAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\CustomerAddressCatalogCollection;
use App\Http\Resources\Api\v1\CustomerAddressCatalogResource;
use App\Models\CustomerAddressCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerAddressCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $customerAddressCatalogs = CustomerAddressCatalog::all();

        return new CustomerAddressCatalogCollection($customerAddressCatalogs);
    }

    public function store(CustomerAddressCatalogStoreRequest $request): Response
    {
        $customerAddressCatalog = CustomerAddressCatalog::create($request->validated());

        return new CustomerAddressCatalogResource($customerAddressCatalog);
    }

    public function show(Request $request, CustomerAddressCatalog $customerAddressCatalog): Response
    {
        return new CustomerAddressCatalogResource($customerAddressCatalog);
    }

    public function update(CustomerAddressCatalogUpdateRequest $request, CustomerAddressCatalog $customerAddressCatalog): Response
    {
        $customerAddressCatalog->update($request->validated());

        return new CustomerAddressCatalogResource($customerAddressCatalog);
    }

    public function destroy(Request $request, CustomerAddressCatalog $customerAddressCatalog): Response
    {
        $customerAddressCatalog->delete();

        return response()->noContent();
    }
}
