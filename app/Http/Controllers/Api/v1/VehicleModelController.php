<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\VehicleModelStoreRequest;
use App\Http\Requests\Api\v1\VehicleModelUpdateRequest;
use App\Http\Resources\Api\v1\VehicleModelCollection;
use App\Http\Resources\Api\v1\VehicleModelResource;
use App\Models\VehicleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mysql_xdevapi\Exception;

class VehicleModelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $vehicleModels = VehicleModel::with('brand')->paginate($perPage);
            return ApiResponse::success($vehicleModels,'Modelos de vehículos recuperados',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(VehicleModelStoreRequest $request): JsonResponse
    {
        try {
            $vehicleModel = (new VehicleModel)->create($request->validated());
            return ApiResponse::success($vehicleModel,'Modelo de vehículo creado con éxito',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,'Error al crear el modelo',500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            return ApiResponse::success($vehicleModel,'Modelo de vehículos recuperado',200);
        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontró el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }
    }

    public function update(VehicleModelUpdateRequest $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            $vehicleModel->update($request->validated());
            return ApiResponse::success($vehicleModel,'Modelo actualizado correctamente',200);

        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontró el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            $vehicleModel->delete();
            return ApiResponse::success(null,'Modelo Eliminado correctamente',200);

        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontró el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }

    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $vehicleModels = VehicleModel::whereIn('id', $ids)->with('brand')->get();
            return ApiResponse::success($vehicleModels, 'Modelos de vehículo recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            VehicleModel::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Modelos de vehículo activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            VehicleModel::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Modelos de vehículo desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            VehicleModel::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Modelos de vehículo eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
