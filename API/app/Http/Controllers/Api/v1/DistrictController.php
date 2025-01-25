<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DistrictStoreRequest;
use App\Http\Requests\Api\v1\DistrictUpdateRequest;
use App\Http\Resources\Api\v1\DistrictCollection;
use App\Http\Resources\Api\v1\DistrictResource;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DistrictController extends Controller
{
    public function index(Request $request): Response
    {
        $districts = District::all();

        return new DistrictCollection($districts);
    }

    public function store(DistrictStoreRequest $request): Response
    {
        $district = District::create($request->validated());

        return new DistrictResource($district);
    }

    public function show(Request $request, District $district): Response
    {
        return new DistrictResource($district);
    }

    public function update(DistrictUpdateRequest $request, District $district): Response
    {
        $district->update($request->validated());

        return new DistrictResource($district);
    }

    public function destroy(Request $request, District $district): Response
    {
        $district->delete();

        return response()->noContent();
    }
}
