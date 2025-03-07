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
        try {
            $roles = Rol::all();
            return ApiResponse::success($roles, 'Roles recuperados exitosamente',200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        try {
//            $rol = Rol::create($request->validated());
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

    public function destroy(Request $request, Role $role): Response
    {
        $role->delete();

        return response()->noContent();
    }
}
