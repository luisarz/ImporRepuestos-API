<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DocumentsTypesProviderStoreRequest;
use App\Http\Requests\Api\v1\DocumentsTypesProviderUpdateRequest;
use App\Http\Resources\Api\v1\DocumentsTypesProviderCollection;
use App\Http\Resources\Api\v1\DocumentsTypesProviderResource;
use App\Models\DocumentsTypesProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentsTypesProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $documentsTypesProviders = (new DocumentsTypesProvider)->paginate($perPage);
            return ApiResponse::success($documentsTypesProviders, 'Tipos de documentos de proveedores obtenidos exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(DocumentsTypesProviderStoreRequest $request): JsonResponse
    {
        try {
            $documentsTypesProvider = (new DocumentsTypesProvider)->create($request->validated());
           return ApiResponse::success(new DocumentsTypesProviderResource($documentsTypesProvider), 'Tipo de documento de proveedor creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $documentsTypesProvider = (new DocumentsTypesProvider)->findOrFail($id);
            if (!$documentsTypesProvider) {
                return ApiResponse::error('Tipo de documento de proveedor no encontrado', 'No se encontró el tipo de documento de proveedor', 404);
            }
            return ApiResponse::success(new DocumentsTypesProviderResource($documentsTypesProvider), 'Tipo de documento de proveedor obtenido exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(DocumentsTypesProviderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $documentsTypesProvider = (new DocumentsTypesProvider)->findOrFail($id);
            if (!$documentsTypesProvider) {
                return ApiResponse::error('Tipo de documento de proveedor no encontrado', 'No se encontró el tipo de documento de proveedor', 404);
            }
            $documentsTypesProvider->update($request->validated());
            return ApiResponse::success(new DocumentsTypesProviderResource($documentsTypesProvider), 'Tipo de documento de proveedor actualizado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $documentsTypesProvider = (new DocumentsTypesProvider)->findOrFail($id);
            if (!$documentsTypesProvider) {
                return ApiResponse::error(null, 'No se encontró el tipo de documento de proveedor', 404);
            }
            $documentsTypesProvider->delete();
           return ApiResponse::success(null, 'Tipo de documento de proveedor eliminado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $documentsTypesProviders = DocumentsTypesProvider::whereIn('id', $ids)->get();
            return ApiResponse::success($documentsTypesProviders, 'Tipos de documento de proveedor recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentsTypesProvider::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de documento de proveedor activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentsTypesProvider::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de documento de proveedor desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DocumentsTypesProvider::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de documento de proveedor eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
