<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\RoleStoreRequest;
use App\Http\Requests\Api\v1\RoleUpdateRequest;
use App\Models\Rol;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto

        try {
            $query = Rol::query();

            // Aplicar filtro de estado si se proporciona
            if ($request->has('status_filter') && $request->input('status_filter') !== '') {
                $statusFilter = $request->input('status_filter');
                $query->where('is_active', $statusFilter);
            }

            $roles = $query->paginate($perPage);
            return ApiResponse::success($roles, 'Roles recuperados exitosamente',200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        try {
            $role = Role::create(['name'=>$request->name,'guard_name'=>$request->guard_name]);
            $role->syncPermissions($request->permission);
            return ApiResponse::success($role, 'Role creado exitosamente',201);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $role=Rol::findOrFail($id);
            return ApiResponse::success($role, 'Rol recuperado exitosamente',200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'Rol no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function update(RoleUpdateRequest $request, $id): JsonResponse
    {
        try {
            $role=Rol::findOrFail($id);
            $role->update($request->validated());
            return ApiResponse::success($role, 'Role actualizado exitosamente',200);

        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'Rol no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $rol=Rol::findOrFail($id);
            $rol->delete();
            return ApiResponse::success(null,'Role eliminado exitosamente',200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null,'Rol no encontrado',404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

        return response()->noContent();
    }

    /**
     * Obtener estadísticas de los roles
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Rol::count();
            $active = Rol::where('is_active', 1)->count();
            $inactive = Rol::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Obtener roles por IDs (para exportación)
     */
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $roles = Rol::whereIn('id', $ids)->get();
            return ApiResponse::success($roles, 'Roles recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Activar múltiples roles
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Rol::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Roles activados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Desactivar múltiples roles
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Rol::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Roles desactivados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Eliminar múltiples roles
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Rol::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Roles eliminados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
