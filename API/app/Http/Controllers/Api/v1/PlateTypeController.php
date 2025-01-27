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
            $plateTypes = PlateType::all();
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
}
