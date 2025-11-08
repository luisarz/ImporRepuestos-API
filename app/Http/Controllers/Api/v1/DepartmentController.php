<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DepartmentStoreRequest;
use App\Http\Requests\Api\v1\DepartmentUpdateRequest;
use App\Http\Resources\Api\v1\DepartmentCollection;
use App\Http\Resources\Api\v1\DepartmentResource;
use App\Models\Department;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto

            $departments = Department::with('country')->paginate($perPage);
            return ApiResponse::success($departments, 'Departamentos recuperados exitosamente', 200);
        }catch (\Exception $e){
           return ApiResponse::error($e->getMessage(),'Ocurrió un error al recuperar la información', 500);
        }
    }

    public function store(DepartmentStoreRequest $request): JsonResponse
    {
        try {
            $department = (new Department)->create($request->validated());
            return ApiResponse::success(new DepartmentResource($department), 'Departamento creado exitosamente', 201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al crear el departamento', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $department = Department::with('country')->findOrFail($id);
            return ApiResponse::success(new DepartmentResource($department),'Departamento recuperado exitosamente.', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar la información.', 500);
        }
    }


    public function update(DepartmentUpdateRequest $request, $id): JsonResponse
    {
        try {
            $department = (new Department)->findOrFail($id);
            $department->update($request->validated());
          return ApiResponse::success(new DepartmentResource($department), 'Departamento actualizado exitosamente.', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al actualizar el departamento.', 500);
        }


    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $department = (new Department)->findOrFail($id);
            $department->delete();
            return ApiResponse::success(null,'Departamento eliminado exitosamente.', 204);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al eliminar el departamento.', 500);
        }
    }

    // Estadísticas
    public function stats(): JsonResponse
    {
        try {
            $total = Department::count();
            $active = Department::where('is_active', 1)->count();
            $inactive = Department::where('is_active', 0)->count();

            return ApiResponse::success([
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ], 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            $items = Department::whereIn('id', $ids)->get();

            return ApiResponse::success($items, 'Elementos recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Department::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Elementos activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Department::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Elementos desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Department::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Elementos eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
