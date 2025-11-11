<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DestinationEnvironment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationEnvironmentController extends Controller
{
    public function index(Request $request): JsonResponse
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

            $query = DestinationEnvironment::query();

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

            $environments = $query->paginate($perPage);
            return ApiResponse::success($environments, 'Ambientes de destino recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:2|unique:destination_environments,code',
                'description' => 'required|string|max:100',
                'is_active' => 'boolean',
            ]);

            $environment = DestinationEnvironment::create($validated);
            return ApiResponse::success($environment, 'Ambiente de destino creado exitosamente', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $environment = DestinationEnvironment::findOrFail($id);
            return ApiResponse::success($environment, 'Ambiente de destino recuperado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Ambiente de destino no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $environment = DestinationEnvironment::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:2|unique:destination_environments,code,' . $id,
                'description' => 'required|string|max:100',
                'is_active' => 'boolean',
            ]);

            $environment->update($validated);
            return ApiResponse::success($environment, 'Ambiente de destino actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Ambiente de destino no encontrado', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $environment = DestinationEnvironment::findOrFail($id);
            $environment->delete();
            return ApiResponse::success(null, 'Ambiente de destino eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Ambiente de destino no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = DestinationEnvironment::count();
            $active = DestinationEnvironment::where('is_active', 1)->count();
            $inactive = DestinationEnvironment::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $environments = DestinationEnvironment::whereIn('id', $ids)->get();
            return ApiResponse::success($environments, 'Ambientes de destino recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DestinationEnvironment::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Ambientes de destino activados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DestinationEnvironment::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Ambientes de destino desactivados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            DestinationEnvironment::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Ambientes de destino eliminados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
