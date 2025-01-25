<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerDocumentsTypeStoreRequest;
use App\Http\Requests\Api\v1\CustomerDocumentsTypeUpdateRequest;
use App\Http\Resources\Api\v1\CustomerDocumentsTypeCollection;
use App\Http\Resources\Api\v1\CustomerDocumentsTypeResource;
use App\Models\CustomerDocumentsType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerDocumentsTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $customerDocumentsTypes = CustomerDocumentsType::all();

        return new CustomerDocumentsTypeCollection($customerDocumentsTypes);
    }

    public function store(CustomerDocumentsTypeStoreRequest $request): Response
    {
        $customerDocumentsType = CustomerDocumentsType::create($request->validated());

        return new CustomerDocumentsTypeResource($customerDocumentsType);
    }

    public function show(Request $request, CustomerDocumentsType $customerDocumentsType): Response
    {
        return new CustomerDocumentsTypeResource($customerDocumentsType);
    }

    public function update(CustomerDocumentsTypeUpdateRequest $request, CustomerDocumentsType $customerDocumentsType): Response
    {
        $customerDocumentsType->update($request->validated());

        return new CustomerDocumentsTypeResource($customerDocumentsType);
    }

    public function destroy(Request $request, CustomerDocumentsType $customerDocumentsType): Response
    {
        $customerDocumentsType->delete();

        return response()->noContent();
    }
}
