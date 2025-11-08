<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DocumentTypeStoreRequest;
use App\Http\Requests\Api\v1\DocumentTypeUpdateRequest;
use App\Http\Resources\Api\v1\DocumentTypeCollection;
use App\Http\Resources\Api\v1\DocumentTypeResource;
use App\Models\DocumentType;
use App\Models\OperationCondition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $operationConditions = DocumentType::where('name', 'like', "%$search%")->paginate($perPage);
            return ApiResponse::success($operationConditions, 'Operation conditions retrieved successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while retrieving the operation conditions', 500);
        }

    }

    public function store(DocumentTypeStoreRequest $request): JsonResponse
    {
        try {
            $documentType = DocumentType::create($request->validated());
            return ApiResponse::success($documentType, 'Document type created successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while creating the document type', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $documentType = DocumentType::findorfail($id);
            return ApiResponse::success($documentType, 'Document type retrieved successfully', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Document type does not exist', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while retrieving the document type', 500);
        }
    }

    public function update(DocumentTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $documentType = (new \App\Models\DocumentType)->findOrFail($id);
            $documentType->update($request->validated());
            return ApiResponse::success($documentType, 'Document type updated successfully', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'An error occurred while updating the document type', 500);
        }
    }

    public function destroy(Request $request, DocumentType $documentType): Response
    {
        $documentType->delete();

        return response()->noContent();
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $items = DocumentType::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Documentos tributarios recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Documentos tributarios activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Documentos tributarios desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Documentos tributarios eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
