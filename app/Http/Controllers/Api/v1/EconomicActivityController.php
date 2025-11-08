<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EconomicActivityStoreRequest;
use App\Http\Requests\Api\v1\EconomicActivityUpdateRequest;
use App\Http\Resources\Api\v1\EconomicActivityCollection;
use App\Http\Resources\Api\v1\EconomicActivityResource;
use App\Models\EconomicActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EconomicActivityController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto
            $economicActivities = EconomicActivity::paginate($perPage);
            return ApiResponse::success(new EconomicActivityCollection($economicActivities),'Economic Activities retrieved successfully', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'error al recuperar datos', 500);

        }


    }

    public function store(EconomicActivityStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $economicActivity = (new \App\Models\EconomicActivity)->create($request->validated());
            return ApiResponse::success(new EconomicActivityResource($economicActivity),'Actividad economica creada correctamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'error al guardar datos', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $economicActivity = EconomicActivity::findOrFail($id);
            if(!$economicActivity){
                return ApiResponse::error('','Actividad economica no encontrada', 404);
            }
            return ApiResponse::success(new EconomicActivityResource($economicActivity),'Actividad economica recuperada correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'error al recuperar datos', 500);
        }
    }

    public function update(EconomicActivityUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $economicActivity = EconomicActivity::findOrFail($id);
            if(!$economicActivity){
                return ApiResponse::error('','Actividad economica no encontrada', 404);
            }
            $economicActivity->update($request->validated());
           return ApiResponse::success(new EconomicActivityResource($economicActivity),'Actividad economica actualizada correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'error al actualizar datos', 500);
        }
    }

    public function destroy(Request $request, EconomicActivity $economicActivity): Response
    {
        $economicActivity->delete();

        return response()->noContent();
    }

    public function stats(): \Illuminate\Http\JsonResponse
    {
        try {
            $total = EconomicActivity::count();
            $active = EconomicActivity::where('is_active', 1)->count();
            $inactive = EconomicActivity::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $activities = EconomicActivity::whereIn('id', $ids)->get();
            return ApiResponse::success($activities, 'Actividades económicas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            EconomicActivity::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Actividades económicas activadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            EconomicActivity::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Actividades económicas desactivadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            EconomicActivity::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Actividades económicas eliminadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
