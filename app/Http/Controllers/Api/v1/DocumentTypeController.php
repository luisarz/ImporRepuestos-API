<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DocumentTypeStoreRequest;
use App\Http\Requests\Api\v1\DocumentTypeUpdateRequest;
use App\Http\Resources\Api\v1\DocumentTypeCollection;
use App\Http\Resources\Api\v1\DocumentTypeResource;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $documentTypes = DocumentType::all();

        return new DocumentTypeCollection($documentTypes);
    }

    public function store(DocumentTypeStoreRequest $request): Response
    {
        $documentType = DocumentType::create($request->validated());

        return new DocumentTypeResource($documentType);
    }

    public function show(Request $request, DocumentType $documentType): Response
    {
        return new DocumentTypeResource($documentType);
    }

    public function update(DocumentTypeUpdateRequest $request, DocumentType $documentType): Response
    {
        $documentType->update($request->validated());

        return new DocumentTypeResource($documentType);
    }

    public function destroy(Request $request, DocumentType $documentType): Response
    {
        $documentType->delete();

        return response()->noContent();
    }
}
