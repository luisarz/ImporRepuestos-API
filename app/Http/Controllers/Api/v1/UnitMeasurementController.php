<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UnitMeasurementStoreRequest;
use App\Http\Requests\Api\v1\UnitMeasurementUpdateRequest;
use App\Http\Resources\Api\v1\UnitMeasurementCollection;
use App\Http\Resources\Api\v1\UnitMeasurementResource;
use App\Models\UnitMeasurement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnitMeasurementController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        try {
            $perPage = $request->input('per_page', 10);
            $unitMeasurements = UnitMeasurement::paginate($perPage);
            return ApiResponse::success($unitMeasurements, 'Unidades de medida obtenidas correctamente',200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('No se encontraron unidades de medida', 404);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener las unidades de medida'], 500);
        }
    }

    public function store(UnitMeasurementStoreRequest $request): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->create($request->validated());
            return ApiResponse::success($unitMeasurement, 'Unidad de medida creada correctamente', 200);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la unidad de medida'], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            return ApiResponse::success($unitMeasurement, 'Unidad de medida obtenida correctamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al obtener la unidad de medida', 500);
        }
    }

    public function update(UnitMeasurementUpdateRequest $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            $unitMeasurement->update($request->validated());
         return ApiResponse::success($unitMeasurement, 'Unidad de medida actualizada correctamente', 200);
        } catch (ModelNotFoundException $e) {
           return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al actualizar la unidad de medida', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $unitMeasurement = (new \App\Models\UnitMeasurement)->findOrFail($id);
            $unitMeasurement->delete();
            return ApiResponse::success(null, 'Unidad de medida eliminada correctamente', 204);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'No se encontró la unidad de medida', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Error al eliminar la unidad de medida', 500);
        }
    }
}
