<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\FuelTypeStoreRequest;
use App\Http\Requests\Api\v1\FuelTypeUpdateRequest;
use App\Http\Resources\Api\v1\FuelTypeCollection;
use App\Http\Resources\Api\v1\FuelTypeResource;
use App\Models\FuelType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FuelTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $fuelTypes = FuelType::paginate(10);
            return ApiResponse::success($fuelTypes, 'Tipos de combustibles recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(FuelTypeStoreRequest $request): JsonResponse
    {
        try {
            $fuelType = (new FuelType)->create($request->validated());
            return ApiResponse::success($fuelType, 'Tipo de combustible registrado con éxito', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $fuelType = FuelType::findOrfail($id);
            return ApiResponse::success($fuelType, 'Tipo de combustible recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de combustible no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(FuelTypeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $fuelType = (new FuelType)->findOrfail($id);
            $fuelType->update($request->validated());
            return ApiResponse::success($fuelType, 'Tipo de combustible recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de combustible no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $fuelType = (new FuelType)->findOrfail($id);
            $fuelType->delete();
            return ApiResponse::success(null, 'Tipo de combustible recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Tipo de combustible no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }



    }
}
