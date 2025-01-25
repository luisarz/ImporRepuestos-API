<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\SalesHeaderCollection;
use App\Http\Resources\Api\v1\SalesHeaderResource;
use App\Models\SalesHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesHeaderController extends Controller
{
    public function index(Request $request): Response
    {
        $salesHeaders = SalesHeader::all();

        return new SalesHeaderCollection($salesHeaders);
    }

    public function store(SalesHeaderStoreRequest $request): Response
    {
        $salesHeader = SalesHeader::create($request->validated());

        return new SalesHeaderResource($salesHeader);
    }

    public function show(Request $request, SalesHeader $salesHeader): Response
    {
        return new SalesHeaderResource($salesHeader);
    }

    public function update(SalesHeaderUpdateRequest $request, SalesHeader $salesHeader): Response
    {
        $salesHeader->update($request->validated());

        return new SalesHeaderResource($salesHeader);
    }

    public function destroy(Request $request, SalesHeader $salesHeader): Response
    {
        $salesHeader->delete();

        return response()->noContent();
    }
}
