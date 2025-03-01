<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalePaymentDetailStoreRequest;
use App\Http\Requests\Api\v1\SalePaymentDetailUpdateRequest;
use App\Http\Resources\Api\v1\SalePaymentDetailCollection;
use App\Http\Resources\Api\v1\SalePaymentDetailResource;
use App\Models\SalePaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalePaymentDetailController extends Controller
{
    public function index(Request $request): Response
    {
        $salePaymentDetails = SalePaymentDetail::all();

        return new SalePaymentDetailCollection($salePaymentDetails);
    }

    public function store(SalePaymentDetailStoreRequest $request): Response
    {
        $salePaymentDetail = SalePaymentDetail::create($request->validated());

        return new SalePaymentDetailResource($salePaymentDetail);
    }

    public function show(Request $request, SalePaymentDetail $salePaymentDetail): Response
    {
        return new SalePaymentDetailResource($salePaymentDetail);
    }

    public function update(SalePaymentDetailUpdateRequest $request, SalePaymentDetail $salePaymentDetail): Response
    {
        $salePaymentDetail->update($request->validated());

        return new SalePaymentDetailResource($salePaymentDetail);
    }

    public function destroy(Request $request, SalePaymentDetail $salePaymentDetail): Response
    {
        $salePaymentDetail->delete();

        return response()->noContent();
    }
}
