<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EconomicActivityStoreRequest;
use App\Http\Requests\Api\v1\EconomicActivityUpdateRequest;
use App\Http\Resources\Api\v1\EconomicActivityCollection;
use App\Http\Resources\Api\v1\EconomicActivityResource;
use App\Models\EconomicActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EconomicActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $economicActivities = EconomicActivity::all();

        return new EconomicActivityCollection($economicActivities);
    }

    public function store(EconomicActivityStoreRequest $request): Response
    {
        $economicActivity = EconomicActivity::create($request->validated());

        return new EconomicActivityResource($economicActivity);
    }

    public function show(Request $request, EconomicActivity $economicActivity): Response
    {
        return new EconomicActivityResource($economicActivity);
    }

    public function update(EconomicActivityUpdateRequest $request, EconomicActivity $economicActivity): Response
    {
        $economicActivity->update($request->validated());

        return new EconomicActivityResource($economicActivity);
    }

    public function destroy(Request $request, EconomicActivity $economicActivity): Response
    {
        $economicActivity->delete();

        return response()->noContent();
    }
}
