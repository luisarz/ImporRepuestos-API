<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\VehicleStoreRequest;
use App\Http\Requests\Api\v1\VehicleUpdateRequest;
use App\Http\Resources\Api\v1\VehicleCollection;
use App\Http\Resources\Api\v1\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        try {
            $perPage = $request->input('per_page', 10);

            $vehicles=Vehicle::with('model','model.brand','fuelType','plateType')->paginate($perPage);
            return ApiResponse::success($vehicles,'Vehiculos recuperados',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,'Ocurrió un error',500);
        }
    }

    public function store(VehicleStoreRequest $request): JsonResponse
    {
        try {
            $vehicle = (new Vehicle)->create($request->validated());
            return ApiResponse::success($vehicle,'Vehiculo almacenado de manera exitosa',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }

    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $vehicle= (new Vehicle)->findOrfail($id);
            return ApiResponse::success($vehicle,'Vehiculo modificado de manera exitosa',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Vehiculo no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',200);
        }
    }

    public function update(VehicleUpdateRequest $request, $id): JsonResponse
    {
        try {
            $vehicle= (new Vehicle)->findOrfail($id);
            $vehicle->update($request->validated());
            return ApiResponse::success($vehicle,'Vehiculo modificado de manera exitosa',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Vehiculo no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',200);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {


        try {
            $vehicle= (new Vehicle)->findOrfail($id);
            $vehicle->delete();
            return ApiResponse::success(null,'Vehiculo modificado de manera exitosa',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Vehiculo no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrio un error',200);
        }
    }
}
