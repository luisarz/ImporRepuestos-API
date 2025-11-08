<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PlateTypeStoreRequest;
use App\Http\Requests\Api\v1\PlateTypeUpdateRequest;
use App\Http\Resources\Api\v1\PlateTypeCollection;
use App\Http\Resources\Api\v1\PlateTypeResource;
use App\Models\PlateType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlateTypeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $plateTypes = PlateType::paginate($perPage);
            return ApiResponse::success($plateTypes, 'Tipos de placas recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(PlateTypeStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $plateType = (new PlateType)->create($request->validated());
            return ApiResponse::success($plateType, 'Tipo de placa creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $plateType = PlateType::findOrFail($id);
            return ApiResponse::success($plateType, 'Tipo de placa recuperado', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de placa no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(PlateTypeUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $plateType = PlateType::findOrFail($id);
            $plateType->update($request->validated());
            return ApiResponse::success($plateType, 'Tipo de placa actualizado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de placa no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $plateType = PlateType::findOrFail($id);
            $plateType->delete();
            return ApiResponse::success(null, 'Tipo de placa eliminado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de placa no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $plateTypes = PlateType::whereIn('id', $ids)->get();
            return ApiResponse::success($plateTypes, 'Tipos de placa recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PlateType::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Tipos de placa activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PlateType::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Tipos de placa desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            PlateType::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Tipos de placa eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
