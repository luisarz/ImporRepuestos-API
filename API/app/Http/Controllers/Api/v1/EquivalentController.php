<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EquivalentStoreRequest;
use App\Http\Requests\Api\v1\EquivalentUpdateRequest;
use App\Http\Resources\Api\v1\EquivalentCollection;
use App\Http\Resources\Api\v1\EquivalentResource;
use App\Models\Equivalent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EquivalentController extends Controller
{
    public function index(Request $request): Response
    {
        $equivalents = Equivalent::all();

        return new EquivalentCollection($equivalents);
    }

    public function store(EquivalentStoreRequest $request): Response
    {
        $equivalent = Equivalent::create($request->validated());

        return new EquivalentResource($equivalent);
    }

    public function show(Request $request, Equivalent $equivalent): Response
    {
        return new EquivalentResource($equivalent);
    }

    public function update(EquivalentUpdateRequest $request, Equivalent $equivalent): Response
    {
        $equivalent->update($request->validated());

        return new EquivalentResource($equivalent);
    }

    public function destroy(Request $request, Equivalent $equivalent): Response
    {
        $equivalent->delete();

        return response()->noContent();
    }
}
