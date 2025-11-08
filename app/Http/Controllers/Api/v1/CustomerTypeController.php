<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerTypeStoreRequest;
use App\Http\Requests\Api\v1\CustomerTypeUpdateRequest;
use App\Http\Resources\Api\v1\CustomerTypeCollection;
use App\Http\Resources\Api\v1\CustomerTypeResource;
use App\Models\CustomerType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $customerTypes = CustomerType::paginate($perPage);
            return ApiResponse::success($customerTypes, 'Tipos de clientes, recuperados de manera exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(CustomerTypeStoreRequest $request): JsonResponse
    {
        try {
            $customerType = (new CustomerType)->create($request->validated());
            return ApiResponse::success($customerType, 'Tipo de cliente creado', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrio un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customerType = (new CustomerType)->findOrFail($id);
            return ApiResponse::success($customerType, 'Tipo de cliente recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de cliente no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $customerType = (new CustomerType)->findOrFail($id);
            $customerType->update($request->validated());
            return ApiResponse::success($customerType, 'Tipo de cliente recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de cliente no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {

        try {
            $customerType = (new CustomerType)->findOrFail($id);
            $customerType->delete();
            return ApiResponse::success(null, 'Tipo de cliente recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de cliente no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = CustomerType::count();
            $active = CustomerType::where('is_active', 1)->count();
            $inactive = CustomerType::where('is_active', 0)->count();

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
            $customerTypes = CustomerType::whereIn('id', $ids)->get();
            return ApiResponse::success($customerTypes, 'Tipos de cliente recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de cliente activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de cliente desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            CustomerType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de cliente eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
