<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchStoreRequest;
use App\Http\Requests\Api\v1\BatchUpdateRequest;
use App\Http\Resources\Api\v1\BatchCollection;
use App\Http\Resources\Api\v1\BatchResource;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BatchController extends Controller
{
    public function index(Request $request): Response
    {
        $batches = Batch::all();

        return new BatchCollection($batches);
    }

    public function store(BatchStoreRequest $request): Response
    {
        $batch = Batch::create($request->validated());

        return new BatchResource($batch);
    }

    public function show(Request $request, Batch $batch): Response
    {
        return new BatchResource($batch);
    }

    public function update(BatchUpdateRequest $request, Batch $batch): Response
    {
        $batch->update($request->validated());

        return new BatchResource($batch);
    }

    public function destroy(Request $request, Batch $batch): Response
    {
        $batch->delete();

        return response()->noContent();
    }
}
