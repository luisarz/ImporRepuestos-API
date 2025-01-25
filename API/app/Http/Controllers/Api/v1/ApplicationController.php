<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ApplicationStoreRequest;
use App\Http\Requests\Api\v1\ApplicationUpdateRequest;
use App\Http\Resources\Api\v1\ApplicationCollection;
use App\Http\Resources\Api\v1\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = Application::all();

        return new ApplicationCollection($applications);
    }

    public function store(ApplicationStoreRequest $request): Response
    {
        $application = Application::create($request->validated());

        return new ApplicationResource($application);
    }

    public function show(Request $request, Application $application): Response
    {
        return new ApplicationResource($application);
    }

    public function update(ApplicationUpdateRequest $request, Application $application): Response
    {
        $application->update($request->validated());

        return new ApplicationResource($application);
    }

    public function destroy(Request $request, Application $application): Response
    {
        $application->delete();

        return response()->noContent();
    }
}
