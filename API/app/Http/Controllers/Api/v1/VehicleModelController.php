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
            $vehicleModels = VehicleModel::paginate(10);
            return ApiResponse::success($vehicleModels,'Modelos de vehiculos recuperados',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(VehicleModelStoreRequest $request): JsonResponse
    {
        try {
            $vehicleModel = (new VehicleModel)->create($request->validated());
            return ApiResponse::success($vehicleModel,'Modelo de vehiculo creado con éxito',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,'Error al crear el modelo',500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            return ApiResponse::success($vehicleModel,'Modelo de vehiculo recuperado',200);
        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontro el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',500);
        }
    }

    public function update(VehicleModelUpdateRequest $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            $vehicleModel->update($request->validated());
            return ApiResponse::success($vehicleModel,'Modelo actualizado correctamente',200);

        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontro el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $vehicleModel= (new VehicleModel)->findOrFail($id);
            $vehicleModel->delete();
            return ApiResponse::success(null,'Modelo Eliminado correctamente',200);

        }catch (ModelNotFoundException $exception){
            return ApiResponse::error(null,'No se encontro el modelo que buscas',400);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',500);
        }

    }
}
