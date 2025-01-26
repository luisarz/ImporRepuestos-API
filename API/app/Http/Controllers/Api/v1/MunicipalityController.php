<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\MunicipalityStoreRequest;
use App\Http\Requests\Api\v1\MunicipalityUpdateRequest;
use App\Http\Resources\Api\v1\MunicipalityCollection;
use App\Http\Resources\Api\v1\MunicipalityResource;
use App\Models\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class MunicipalityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $municipalities = Municipality::with('department')->paginate(10);
            return ApiResponse::success($municipalities, 'Municipios recuperados con éxito', 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(MunicipalityStoreRequest $request): JsonResponse
    {
        try {
            $municipality = (new \App\Models\Municipality)->create($request->validated());
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio creado con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al crear el municipio', 500);
        }
    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $municipality = Municipality::with('department')->find($id);
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio recuperado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al recuperar el municipio', 500);
        }
    }

    public function update(MunicipalityUpdateRequest $request, $id): JsonResponse
    {
        try {
            $municipality = (new Municipality)->findOrFail($id);
            if (!$municipality) {
                return ApiResponse::error('','Municipio no encontrado', 404);
            }
            $municipality->update($request->validated());
            return ApiResponse::success(new MunicipalityResource($municipality), 'Municipio actualizado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al actualizar el municipio', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $municipality = (new Municipality)->findOrFail($id);
            if (!$municipality) {
                return ApiResponse::error('','Municipio no encontrado', 404);
            }
            $municipality->delete();
            return ApiResponse::success('','Municipio eliminado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al eliminar el municipio', 500);
        }
    }
}
