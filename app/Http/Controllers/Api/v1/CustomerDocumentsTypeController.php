<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerDocumentsTypeStoreRequest;
use App\Http\Requests\Api\v1\CustomerDocumentsTypeUpdateRequest;
use App\Http\Resources\Api\v1\CustomerDocumentsTypeCollection;
use App\Http\Resources\Api\v1\CustomerDocumentsTypeResource;
use App\Models\CustomerDocumentsType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerDocumentsTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $customerDocumentsTypes = CustomerDocumentsType::paginate($perPage);
            return ApiResponse::success($customerDocumentsTypes, 'Tipos de documento de cliente recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(CustomerDocumentsTypeStoreRequest $request): JsonResponse
    {
        try {
            $customerDocumentsType = (new CustomerDocumentsType)->create($request->validated());
            return ApiResponse::success($customerDocumentsType, 'Tipo de documento creado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customerDocumentsType = (new CustomerDocumentsType)->findOrFail($id);
            return ApiResponse::success($customerDocumentsType, 'Tipo de documento recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de documento no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerDocumentsTypeUpdateRequest $request, $id): JsonResponse
    {

        try {
            $customerDocumentsType = (new CustomerDocumentsType)->findOrFail($id);
            $customerDocumentsType->update($request->validated());
            return ApiResponse::success($customerDocumentsType, 'Tipo de documento Actualizado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de documento no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {

        try {
            $customerDocumentsType = (new CustomerDocumentsType)->findOrFail($id);
            $customerDocumentsType->delete();
            return ApiResponse::success(null, 'Tipo de documento Eliminar', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de documento no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = CustomerDocumentsType::count();
            $active = CustomerDocumentsType::where('is_active', 1)->count();
            $inactive = CustomerDocumentsType::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $items = CustomerDocumentsType::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Tipos de documento recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerDocumentsType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de documento activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerDocumentsType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de documento desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerDocumentsType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de documento eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
