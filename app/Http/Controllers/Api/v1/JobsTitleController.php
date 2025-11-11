<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\JobsTitleStoreRequest;
use App\Http\Requests\Api\v1\JobsTitleUpdateRequest;
use App\Http\Resources\Api\v1\JobsTitleCollection;
use App\Http\Resources\Api\v1\JobsTitleResource;
use App\Models\JobsTitle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobsTitleController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = JobsTitle::query();

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro de estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'code', 'description', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $jobsTitles = $query->paginate($perPage);
            return ApiResponse::success($jobsTitles, 'Cargos laborales recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(JobsTitleStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->create($request->validated());
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral recuperado', 200);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);

        }
    }

    public function update(JobsTitleUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            $jobsTitle->update($request->validated());
            return ApiResponse::success(new JobsTitleResource($jobsTitle), 'Cargo laboral actualizado', 200);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitle = (new \App\Models\JobsTitle)->findOrFail($id);
            $jobsTitle->delete();
            return ApiResponse::success(null, 'Cargo laboral eliminado', 204);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cargo laboral no encontrado', 'Cargo laboral no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener todos los puestos de trabajo activos (sin paginar)
     * Para uso en selects y formularios
     */
    public function getAll(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $jobsTitles = JobsTitle::where('is_active', 1)
                ->select('id', 'code', 'description')
                ->orderBy('description')
                ->get();
            return ApiResponse::success($jobsTitles, 'Cargos laborales recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): \Illuminate\Http\JsonResponse
    {
        try {
            $total = JobsTitle::count();
            $active = JobsTitle::where('is_active', 1)->count();
            $inactive = JobsTitle::where('is_active', 0)->count();

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
            $items = JobsTitle::whereIn('id', $ids)->get();
            return ApiResponse::success($items, 'Cargos laborales recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            JobsTitle::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Cargos laborales activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            JobsTitle::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Cargos laborales desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            JobsTitle::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Cargos laborales eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
