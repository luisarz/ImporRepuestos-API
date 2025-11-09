<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * GET /api/v1/menu?employee_id={id}
     * Obtener módulos permitidos para el usuario
     * Reemplaza el endpoint anterior que usaba modulo_rol
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id'
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            $user = User::where('employee_id', $employee->id)->first();

            if (!$user) {
                return ApiResponse::error('Usuario no encontrado para este empleado', 'Error', 404);
            }

            // Obtener módulos permitidos basados en permisos de Spatie
            $allowedModules = $user->getAllowedModules();

            return ApiResponse::success($allowedModules, 'Menú recuperado exitosamente', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener menú', 500);
        }
    }

    /**
     * GET /api/v1/menu/permissions?employee_id={id}
     * Obtener permisos del usuario
     * Útil para guardar en localStorage y verificar permisos en el frontend
     */
    public function permissions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id'
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            $user = User::where('employee_id', $employee->id)->first();

            if (!$user) {
                return ApiResponse::error('Usuario no encontrado para este empleado', 'Error', 404);
            }

            $permissions = $user->getAllPermissions();

            $response = [
                'permissions' => $permissions->pluck('name')->toArray(),
                'roles' => $user->getRoleNames()->toArray(),
                'is_super_admin' => $user->hasRole('Super Admin'),
            ];

            return ApiResponse::success($response, 'Permisos recuperados exitosamente', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 'Error de validación', 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener permisos', 500);
        }
    }
}
