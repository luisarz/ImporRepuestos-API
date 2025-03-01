<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\QuotePurchaseStoreRequest;
use App\Http\Requests\Api\v1\QuotePurchaseUpdateRequest;
use App\Http\Resources\Api\v1\QuotePurchaseCollection;
use App\Http\Resources\Api\v1\QuotePurchaseResource;
use App\Models\QuotePurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotePurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $quotePurchases = QuotePurchase::all();

        return new QuotePurchaseCollection($quotePurchases);
    }

    public function store(QuotePurchaseStoreRequest $request): Response
    {
        $quotePurchase = QuotePurchase::create($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function show(Request $request, QuotePurchase $quotePurchase): Response
    {
        return new QuotePurchaseResource($quotePurchase);
    }

    public function update(QuotePurchaseUpdateRequest $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->update($request->validated());

        return new QuotePurchaseResource($quotePurchase);
    }

    public function destroy(Request $request, QuotePurchase $quotePurchase): Response
    {
        $quotePurchase->delete();

        return response()->noContent();
    }
}
