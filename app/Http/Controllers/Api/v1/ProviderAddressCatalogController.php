<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\ProviderAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\ProviderAddressCatalogCollection;
use App\Http\Resources\Api\v1\ProviderAddressCatalogResource;
use App\Models\ProviderAddressCatalog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderAddressCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $providerAddressCatalogs = ProviderAddressCatalog::paginate($perPage);
            return ApiResponse::success($providerAddressCatalogs, 'Direcciones de proveedor listados correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(ProviderAddressCatalogStoreRequest $request): JsonResponse
    {
        try {
            $providerAddressCatalog = (new \App\Models\ProviderAddressCatalog)->create($request->validated());
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor creada correctamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor obtenida correctamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
        } catch (\Exception $e) {
            // Capturamos cualquier otro tipo de error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }


    public function update(ProviderAddressCatalogUpdateRequest $request,$id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);

            $providerAddressCatalog->update($request->validated());
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor actualizada correctamente', 200);

        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);
            if(!$providerAddressCatalog) {
                return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
            }
            $providerAddressCatalog->delete();
           return ApiResponse::success(null, 'Dirección de proveedor eliminada correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
