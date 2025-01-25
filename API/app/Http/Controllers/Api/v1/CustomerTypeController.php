<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerTypeStoreRequest;
use App\Http\Requests\Api\v1\CustomerTypeUpdateRequest;
use App\Http\Resources\Api\v1\CustomerTypeCollection;
use App\Http\Resources\Api\v1\CustomerTypeResource;
use App\Models\CustomerType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $customerTypes = CustomerType::all();

        return new CustomerTypeCollection($customerTypes);
    }

    public function store(CustomerTypeStoreRequest $request): Response
    {
        $customerType = CustomerType::create($request->validated());

        return new CustomerTypeResource($customerType);
    }

    public function show(Request $request, CustomerType $customerType): Response
    {
        return new CustomerTypeResource($customerType);
    }

    public function update(CustomerTypeUpdateRequest $request, CustomerType $customerType): Response
    {
        $customerType->update($request->validated());

        return new CustomerTypeResource($customerType);
    }

    public function destroy(Request $request, CustomerType $customerType): Response
    {
        $customerType->delete();

        return response()->noContent();
    }
}
