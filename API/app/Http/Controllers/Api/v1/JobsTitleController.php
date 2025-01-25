<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\JobsTitleStoreRequest;
use App\Http\Requests\Api\v1\JobsTitleUpdateRequest;
use App\Http\Resources\Api\v1\JobsTitleCollection;
use App\Http\Resources\Api\v1\JobsTitleResource;
use App\Models\JobsTitle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobsTitleController extends Controller
{
    public function index(Request $request): Response
    {
        $jobsTitles = JobsTitle::all();

        return new JobsTitleCollection($jobsTitles);
    }

    public function store(JobsTitleStoreRequest $request): Response
    {
        $jobsTitle = JobsTitle::create($request->validated());

        return new JobsTitleResource($jobsTitle);
    }

    public function show(Request $request, JobsTitle $jobsTitle): Response
    {
        return new JobsTitleResource($jobsTitle);
    }

    public function update(JobsTitleUpdateRequest $request, JobsTitle $jobsTitle): Response
    {
        $jobsTitle->update($request->validated());

        return new JobsTitleResource($jobsTitle);
    }

    public function destroy(Request $request, JobsTitle $jobsTitle): Response
    {
        $jobsTitle->delete();

        return response()->noContent();
    }
}
