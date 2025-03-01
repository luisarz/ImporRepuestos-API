<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesDteStoreRequest;
use App\Http\Requests\Api\v1\SalesDteUpdateRequest;
use App\Http\Resources\Api\v1\SalesDteCollection;
use App\Http\Resources\Api\v1\SalesDteResource;
use App\Models\SalesDte;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesDteController extends Controller
{
    public function index(Request $request): Response
    {
        $salesDtes = SalesDte::all();

        return new SalesDteCollection($salesDtes);
    }

    public function store(SalesDteStoreRequest $request): Response
    {
        $salesDte = SalesDte::create($request->validated());

        return new SalesDteResource($salesDte);
    }

    public function show(Request $request, SalesDte $salesDte): Response
    {
        return new SalesDteResource($salesDte);
    }

    public function update(SalesDteUpdateRequest $request, SalesDte $salesDte): Response
    {
        $salesDte->update($request->validated());

        return new SalesDteResource($salesDte);
    }

    public function destroy(Request $request, SalesDte $salesDte): Response
    {
        $salesDte->delete();

        return response()->noContent();
    }
}
