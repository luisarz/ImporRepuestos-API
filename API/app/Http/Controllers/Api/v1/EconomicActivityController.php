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
    public function index(Request $request): EconomicActivityCollection
    {
        $economicActivities = EconomicActivity::paginate(10);

        return new EconomicActivityCollection($economicActivities);
    }

    public function store(EconomicActivityStoreRequest $request): EconomicActivityResource
    {
        $economicActivity = EconomicActivity::create($request->validated());

        return new EconomicActivityResource($economicActivity);
    }

    public function show(Request $request, EconomicActivity $economicActivity): EconomicActivityResource
    {
        return new EconomicActivityResource($economicActivity);
    }

    public function update(EconomicActivityUpdateRequest $request, EconomicActivity $economicActivity): EconomicActivityResource
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
