<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\CustomerAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\CustomerAddressCatalogCollection;
use App\Http\Resources\Api\v1\CustomerAddressCatalogResource;
use App\Models\CustomerAddressCatalog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerAddressCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $customerAddressCatalogs = CustomerAddressCatalog::paginate($perPage);
            return ApiResponse::success($customerAddressCatalogs, 'Registros obtenidos correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(CustomerAddressCatalogStoreRequest $request): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::create($request->validated());
            return ApiResponse::success($customerAddressCatalog, 'Registro creado correctamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            return ApiResponse::success($customerAddressCatalog, 'Registro obtenido correctamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerAddressCatalogUpdateRequest $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            $customerAddressCatalog->update($request->validated());
            return ApiResponse::success($customerAddressCatalog, 'Registro actualizado correctamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            $customerAddressCatalog->delete();
            return ApiResponse::success(null, 'Registro eliminado correctamente', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 'Ocurrió un error', 500);
        }

    }
}
