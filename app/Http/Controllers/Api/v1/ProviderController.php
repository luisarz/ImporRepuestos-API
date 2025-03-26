<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderStoreRequest;
use App\Http\Requests\Api\v1\ProviderUpdateRequest;
use App\Http\Resources\Api\v1\ProviderCollection;
use App\Http\Resources\Api\v1\ProviderResource;
use App\Http\Resources\Api\v1\ProvidersTypeCollection;
use App\Http\Resources\Api\v1\WarehouseResource;
use App\Models\Provider;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mysql_xdevapi\Exception;

class ProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $providers = Provider::with('documentType:id,code,description','economicActivity:id,code,description','providerType:id,code,description')->paginate($perPage);
          return ApiResponse::success($providers, 'Proveedores listados correctamente');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function store(ProviderStoreRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated(); // Validación
            $provider = (new \App\Models\Provider)->create($validated); // Usa los datos validados
            return ApiResponse::success(new ProviderResource($provider), 'Sucursal aperturada de manera exitosa!', 201);
        } catch (QueryException $e) {
            return ApiResponse::error(null, 'Error de base de datos: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $provider = Provider::with('documentType:id,code,description','economicActivity:id,code,description','providerType:id,code:description')->findOrFail($id);
            if(!$provider) {
                return ApiResponse::error(null, 'Proveedor no encontrado', 404);
            }
            return ApiResponse::success(new ProviderResource($provider), 'Proveedor encontrado correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(ProviderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $provider = (new Provider)->findOrFail($id);
            if(!$provider) {
                return ApiResponse::error(null, 'Proveedor no encontrado', 404);
            }
            $provider->update($request->validated());
            return ApiResponse::success(new ProviderResource($provider), 'Proveedor actualizado correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $provider = (new \App\Models\Provider)->findOrFail($id);
            if(!$provider) {
                return ApiResponse::error(null, 'Proveedor no encontrado', 404);
            }
            $provider->delete();
            return ApiResponse::success(null, 'Proveedor eliminado correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}