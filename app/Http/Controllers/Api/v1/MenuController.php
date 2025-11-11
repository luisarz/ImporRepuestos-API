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
     * GET /api/v1/menu
     * Obtener módulos permitidos para el usuario autenticado
     * Usa el usuario del token JWT
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener el usuario autenticado del token JWT
            $user = auth()->user();

            if (!$user) {
                return ApiResponse::error('Usuario no autenticado', 'Error', 401);
            }

            // Obtener módulos permitidos basados en permisos de Spatie
            $allowedModules = $user->getAllowedModules();

            return ApiResponse::success($allowedModules, 'Menú recuperado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener menú', 500);
        }
    }

    /**
     * GET /api/v1/menu/permissions
     * Obtener permisos del usuario autenticado
     * Útil para verificar permisos en el frontend
     */
    public function permissions(Request $request): JsonResponse
    {
        try {
            // Obtener el usuario autenticado del token JWT
            $user = auth()->user();

            if (!$user) {
                return ApiResponse::error('Usuario no autenticado', 'Error', 401);
            }

            $permissions = $user->getAllPermissions();

            $response = [
                'permissions' => $permissions->pluck('name')->toArray(),
                'roles' => $user->getRoleNames()->toArray(),
                'is_super_admin' => $user->hasRole('Super Admin'),
            ];

            return ApiResponse::success($response, 'Permisos recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener permisos', 500);
        }
    }
}
