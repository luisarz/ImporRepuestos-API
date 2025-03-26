<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProvidersTypeStoreRequest;
use App\Http\Requests\Api\v1\ProvidersTypeUpdateRequest;
use App\Http\Resources\Api\v1\ProvidersTypeCollection;
use App\Http\Resources\Api\v1\ProvidersTypeResource;
use App\Models\ProvidersType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProvidersTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $providersTypes = (new ProvidersType)->paginate($perPage);
            return ApiResponse::success($providersTypes, 'Tipos de proveedores obtenidos exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function store(ProvidersTypeStoreRequest $request): JsonResponse
    {
        try {
            $providersType = ProvidersType::create($request->validated());
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor creado exitosamente',201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor obtenido exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(ProvidersTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            $providersType->update($request->validated());
            return ApiResponse::success(new ProvidersTypeResource($providersType), 'Tipo de proveedor actualizado exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $providersType = (new ProvidersType)->findOrFail($id);
            if(!$providersType){
                return ApiResponse::error('Tipo de proveedor no encontrado','No se encontró el tipo de proveedor', 404);
            }
            $providersType->delete();
           return ApiResponse::success(null, 'Tipo de proveedor eliminado exitosamente',204);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }
}
