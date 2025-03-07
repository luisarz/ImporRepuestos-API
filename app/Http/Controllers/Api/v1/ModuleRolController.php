<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ModuleRolStoreRequest;
use App\Http\Requests\Api\v1\ModuleRolUpdateRequest;
use App\Http\Requests\ModuleRolAllowRequest;
use App\Http\Resources\Api\v1\ModuleRolCollection;
use App\Http\Resources\Api\v1\ModuleRolResource;
use App\Models\ModuleRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mysql_xdevapi\Exception;

class ModuleRolController extends Controller
{
    public function store(ModuleRolStoreRequest $request): JsonResponse
    {
        try {
            if ($request->is_active) {
                // Si no existe, lo crea automáticamente
                $moduleRol = ModuleRol::firstOrCreate([
                    'id_module' => $request->id_module,
                    'id_rol' => $request->id_rol
                ], $request->validated());

                return ApiResponse::success($moduleRol, 'Permiso creado exitosamente', 200);
            }

            // Si no está activo, eliminamos el permiso si existe
            $deleted = ModuleRol::where('id_module', $request->id_module)
                ->where('id_rol', $request->id_rol)
                ->delete();

            return ApiResponse::success(null, $deleted ? 'Permiso eliminado exitosamente' : 'No se encontró permiso para eliminar', 200);
        } catch (\Exception $exception) {
            return ApiResponse::error(null, 'Ocurrió un error: ' . $exception->getMessage(), 500);
        }
    }


    public function show($id_rol): JsonResponse
    {
        try {
            $rol = ModuleRol::where('id_rol', $id_rol)->get();
            return ApiResponse::success($rol, 'Permiso recuperado exitosamente', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error(null, 'Permiso no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }


}
