<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerStoreRequest;
use App\Http\Requests\Api\v1\CustomerUpdateRequest;
use App\Http\Resources\Api\v1\CustomerCollection;
use App\Http\Resources\Api\v1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = Customer::all();

        return new CustomerCollection($customers);
    }

    public function store(CustomerStoreRequest $request): Response
    {
        $customer = Customer::create($request->validated());

        return new CustomerResource($customer);
    }

    public function show(Request $request, Customer $customer): Response
    {
        return new CustomerResource($customer);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer): Response
    {
        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Request $request, Customer $customer): Response
    {
        $customer->delete();

        return response()->noContent();
    }
}
