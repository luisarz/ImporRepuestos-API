<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ModuloStoreRequest;
use App\Http\Requests\Api\v1\ModuloUpdateRequest;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;

class ModuloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $modulos =Modulo::paginate(10);
            return ApiResponse::success($modulos, 'Modulos recuperados exitosamente',200);

        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }
    }

    public function store(ModuloStoreRequest $request): JsonResponse
    {
        try {

            $modulo = Modulo::create($request->validated());
            //Crear todos los permisos para el modulo
            $module_name=str_replace(' ','_',strtolower($modulo->nombre)).'_';
            $is_parent = $request->is_padre;

            if(!$is_parent){
                $permissions = [
                    $module_name.'view',
                    $module_name.'view_any',
                    $module_name.'create',
                    $module_name.'update',
                    $module_name.'restore',
                    $module_name.'restore_any',
                    $module_name.'replicate',
                    $module_name.'reorder',
                    $module_name.'delete',
                    $module_name.'delete_any',
                    $module_name.'force_delete',
                    $module_name.'force_delete_any',
                ];

                foreach ($permissions as $permission) {
                    Permission::create(['name'=>$permission,'guard_name'=>'api','module_id'=>$modulo->id]);
                }
                $response=[
                    'module'=>$modulo,
                    'permissions'=>$permissions
                ];
                return ApiResponse::success($response, 'Modulo creado exitosamente',200);
            }


            return ApiResponse::success($modulo, 'Modulo creado exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $Modulo = Modulo::findOrFail($id);
            return ApiResponse::success($Modulo, 'Modulo recuperado exitosamente',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Modulo no encontrado',404);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }
    }

    public function update(ModuloUpdateRequest $request,$id): JsonResponse
    {
        try {
            $modulo=Modulo::findOrFail($id);
            $modulo->update($request->validated());
            return ApiResponse::success($modulo, 'Modulo actualizado exitosamente',200);
        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Modulo no encontrado',404);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $modulo=Modulo::findOrFail($id);
            $modulo->delete();

        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Modulo no encontrado',404);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }

    }
}
