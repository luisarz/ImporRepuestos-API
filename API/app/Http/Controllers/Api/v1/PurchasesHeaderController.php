<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PurchasesHeaderStoreRequest;
use App\Http\Requests\Api\v1\PurchasesHeaderUpdateRequest;
use App\Http\Resources\Api\v1\PurchasesHeaderCollection;
use App\Http\Resources\Api\v1\PurchasesHeaderResource;
use App\Models\PurchasesHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchasesHeaderController extends Controller
{
    public function index(Request $request): Response
    {
        $purchasesHeaders = PurchasesHeader::all();

        return new PurchasesHeaderCollection($purchasesHeaders);
    }

    public function store(PurchasesHeaderStoreRequest $request): Response
    {
        $purchasesHeader = PurchasesHeader::create($request->validated());

        return new PurchasesHeaderResource($purchasesHeader);
    }

    public function show(Request $request, PurchasesHeader $purchasesHeader): Response
    {
        return new PurchasesHeaderResource($purchasesHeader);
    }

    public function update(PurchasesHeaderUpdateRequest $request, PurchasesHeader $purchasesHeader): Response
    {
        $purchasesHeader->update($request->validated());

        return new PurchasesHeaderResource($purchasesHeader);
    }

    public function destroy(Request $request, PurchasesHeader $purchasesHeader): Response
    {
        $purchasesHeader->delete();

        return response()->noContent();
    }
}
