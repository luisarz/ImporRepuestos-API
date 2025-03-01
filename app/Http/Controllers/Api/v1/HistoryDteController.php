<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\HistoryDteStoreRequest;
use App\Http\Requests\Api\v1\HistoryDteUpdateRequest;
use App\Http\Resources\Api\v1\HistoryDteCollection;
use App\Http\Resources\Api\v1\HistoryDteResource;
use App\Models\HistoryDte;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HistoryDteController extends Controller
{
    public function index(Request $request): Response
    {
        $historyDtes = HistoryDte::all();

        return new HistoryDteCollection($historyDtes);
    }

    public function store(HistoryDteStoreRequest $request): Response
    {
        $historyDte = HistoryDte::create($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function show(Request $request, HistoryDte $historyDte): Response
    {
        return new HistoryDteResource($historyDte);
    }

    public function update(HistoryDteUpdateRequest $request, HistoryDte $historyDte): Response
    {
        $historyDte->update($request->validated());

        return new HistoryDteResource($historyDte);
    }

    public function destroy(Request $request, HistoryDte $historyDte): Response
    {
        $historyDte->delete();

        return response()->noContent();
    }
}
