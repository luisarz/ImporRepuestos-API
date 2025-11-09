<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ApplicationStoreRequest;
use App\Http\Requests\Api\v1\ApplicationUpdateRequest;
use App\Http\Resources\Api\v1\ApplicationCollection;
use App\Http\Resources\Api\v1\ApplicationResource;
use App\Models\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $applications = Application::with([
                'product:id,code,barcode,description',
                'vehicle:id,year,chassis', // Asegura que 'vehicle' incluya 'model_id'
//                'vehicle.model:id,model' // Carga 'model' dentro de 'vehicle'
            ])->select('product_id', 'vehicle_id')->paginate($perPage);

            return ApiResponse::success($applications,'Aplicaciones recuperadas',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }

    }

    public function store(ApplicationStoreRequest $request): JsonResponse
    {
        try {
            $application = (new Application)->create($request->validated());
            return ApiResponse::success($application,'Aplicación creada con éxito',200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }


    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $application=Application::with([
                'product:id,code,barcode,description',
                'vehicle:id,year,chassis', // Asegura que 'vehicle' incluya 'model_id'
            ])->select('product_id', 'vehicle_id')->findOrFail($id);
            return ApiResponse::success($application,'Aplicación recuperada exitosamente',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'No se encontró la Aplicación buscada',404);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }
    }

    public function update(ApplicationUpdateRequest $request, $id): JsonResponse
    {
        try {
            $application= (new Application)->findOrFail($id);
            $application->update($request->validated());
            return ApiResponse::success($application,'Aplicación recuperada exitosamente',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'No se encontró la Aplicación buscada',404);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }


    }

    public function destroy(Request $request, $id): JsonResponse
    {

        try {
            $application= (new Application)->findOrFail($id);
            $application->delete();
            return ApiResponse::success(null,'Aplicación eliminada exitosamente',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'No se encontró la Aplicación buscada',404);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error',500);
        }
    }

    public function getApplicationByProduct($id, Request $request): JsonResponse
    {
        try {
            $per_page = $request->input('per_page', 10);
            $applications = Application::with([
                'product:id,code,barcode,description',
                'vehicle:id,year,chassis'
            ])->where('product_id', $id)->paginate($per_page);

            return ApiResponse::success($applications, 'Aplicaciones recuperadas exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No se encontraron aplicaciones', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
