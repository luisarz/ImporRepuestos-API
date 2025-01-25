<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchCodeOrigenStoreRequest;
use App\Http\Requests\Api\v1\BatchCodeOrigenUpdateRequest;
use App\Http\Resources\Api\v1\BatchCodeOrigenCollection;
use App\Http\Resources\Api\v1\BatchCodeOrigenResource;
use App\Models\BatchCodeOrigen;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BatchCodeOrigenController extends Controller
{
    public function index(Request $request): Response
    {
        $batchCodeOrigens = BatchCodeOrigen::all();

        return new BatchCodeOrigenCollection($batchCodeOrigens);
    }

    public function store(BatchCodeOrigenStoreRequest $request): Response
    {
        $batchCodeOrigen = BatchCodeOrigen::create($request->validated());

        return new BatchCodeOrigenResource($batchCodeOrigen);
    }

    public function show(Request $request, BatchCodeOrigen $batchCodeOrigen): Response
    {
        return new BatchCodeOrigenResource($batchCodeOrigen);
    }

    public function update(BatchCodeOrigenUpdateRequest $request, BatchCodeOrigen $batchCodeOrigen): Response
    {
        $batchCodeOrigen->update($request->validated());

        return new BatchCodeOrigenResource($batchCodeOrigen);
    }

    public function destroy(Request $request, BatchCodeOrigen $batchCodeOrigen): Response
    {
        $batchCodeOrigen->delete();

        return response()->noContent();
    }
}
