<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\MenuAllowedRequest;
use App\Models\Employee;
use App\Models\ModuleRol;
use App\Models\User;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuAllowedController extends Controller
{

    public function index(MenuAllowedRequest $request): JsonResponse
    {

        try {
            $empleados = Employee::findOrFail($request->id_employee);

            $user=User::where('employee_id',$empleados->id)->first();

            $Access = ModuleRol::Where("id_rol",$user->id_rol)
                ->join('modulo','modulo.id', '=', 'modulo_rol.id_module')
                ->orderBy('modulo.orden', 'ASC')->get();

            return ApiResponse::success($Access, 'Empleado recuperado exitosamente', 200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(), 500);
        }

    }
}
