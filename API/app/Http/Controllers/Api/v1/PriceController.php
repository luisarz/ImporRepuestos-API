<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PriceStoreRequest;
use App\Http\Requests\Api\v1\PriceUpdateRequest;
use App\Http\Resources\Api\v1\PriceCollection;
use App\Http\Resources\Api\v1\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PriceController extends Controller
{
    public function index(Request $request): Response
    {
        $prices = Price::all();

        return new PriceCollection($prices);
    }

    public function store(PriceStoreRequest $request): Response
    {
        $price = Price::create($request->validated());

        return new PriceResource($price);
    }

    public function show(Request $request, Price $price): Response
    {
        return new PriceResource($price);
    }

    public function update(PriceUpdateRequest $request, Price $price): Response
    {
        $price->update($request->validated());

        return new PriceResource($price);
    }

    public function destroy(Request $request, Price $price): Response
    {
        $price->delete();

        return response()->noContent();
    }
}
