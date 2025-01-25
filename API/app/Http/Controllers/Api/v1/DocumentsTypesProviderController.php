<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DocumentsTypesProviderStoreRequest;
use App\Http\Requests\Api\v1\DocumentsTypesProviderUpdateRequest;
use App\Http\Resources\Api\v1\DocumentsTypesProviderCollection;
use App\Http\Resources\Api\v1\DocumentsTypesProviderResource;
use App\Models\DocumentsTypesProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentsTypesProviderController extends Controller
{
    public function index(Request $request): Response
    {
        $documentsTypesProviders = DocumentsTypesProvider::all();

        return new DocumentsTypesProviderCollection($documentsTypesProviders);
    }

    public function store(DocumentsTypesProviderStoreRequest $request): Response
    {
        $documentsTypesProvider = DocumentsTypesProvider::create($request->validated());

        return new DocumentsTypesProviderResource($documentsTypesProvider);
    }

    public function show(Request $request, DocumentsTypesProvider $documentsTypesProvider): Response
    {
        return new DocumentsTypesProviderResource($documentsTypesProvider);
    }

    public function update(DocumentsTypesProviderUpdateRequest $request, DocumentsTypesProvider $documentsTypesProvider): Response
    {
        $documentsTypesProvider->update($request->validated());

        return new DocumentsTypesProviderResource($documentsTypesProvider);
    }

    public function destroy(Request $request, DocumentsTypesProvider $documentsTypesProvider): Response
    {
        $documentsTypesProvider->delete();

        return response()->noContent();
    }
}
