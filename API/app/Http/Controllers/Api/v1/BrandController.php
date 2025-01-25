<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BrandStoreRequest;
use App\Http\Requests\Api\v1\BrandUpdateRequest;
use App\Http\Resources\Api\v1\BrandCollection;
use App\Http\Resources\Api\v1\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function index(Request $request): Response
    {
        $brands = Brand::all();

        return new BrandCollection($brands);
    }

    public function store(BrandStoreRequest $request): Response
    {
        $brand = Brand::create($request->validated());

        return new BrandResource($brand);
    }

    public function show(Request $request, Brand $brand): Response
    {
        return new BrandResource($brand);
    }

    public function update(BrandUpdateRequest $request, Brand $brand): Response
    {
        $brand->update($request->validated());

        return new BrandResource($brand);
    }

    public function destroy(Request $request, Brand $brand): Response
    {
        $brand->delete();

        return response()->noContent();
    }
}
