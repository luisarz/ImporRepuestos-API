<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerAddressStoreRequest;
use App\Http\Requests\Api\v1\CustomerAddressUpdateRequest;
use App\Http\Resources\Api\v1\CustomerAddressCollection;
use App\Http\Resources\Api\v1\CustomerAddressResource;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerAddressController extends Controller
{
    public function index(Request $request): Response
    {
        $customerAddresses = CustomerAddress::all();

        return new CustomerAddressCollection($customerAddresses);
    }

    public function store(CustomerAddressStoreRequest $request): Response
    {
        $customerAddress = CustomerAddress::create($request->validated());

        return new CustomerAddressResource($customerAddress);
    }

    public function show(Request $request, CustomerAddress $customerAddress): Response
    {
        return new CustomerAddressResource($customerAddress);
    }

    public function update(CustomerAddressUpdateRequest $request, CustomerAddress $customerAddress): Response
    {
        $customerAddress->update($request->validated());

        return new CustomerAddressResource($customerAddress);
    }

    public function destroy(Request $request, CustomerAddress $customerAddress): Response
    {
        $customerAddress->delete();

        return response()->noContent();
    }
}
