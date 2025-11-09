<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * GET /api/v1/permissions
     * Obtener todos los permisos
     */
    public function index(): JsonResponse
    {
        try {
            $permissions = Permission::with('module')->get();
            return ApiResponse::success($permissions, 'Permisos recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener permisos', 500);
        }
    }

    /**
     * GET /api/v1/permissions/grouped
     * Obtener permisos agrupados por módulo
     */
    public function groupedByModule(): JsonResponse
    {
        try {
            $modules = Modulo::where('is_padre', 0)
                ->where('is_active', 1)
                ->with(['permission' => function($query) {
                    $query->orderBy('name');
                }])
                ->orderBy('orden')
                ->get();

            $grouped = $modules->map(function($module) {
                return [
                    'module_id' => $module->id,
                    'module_name' => $module->nombre,
                    'module_icon' => $module->icono ?? 'element-11',
                    'module_route' => $module->ruta,
                    'parent_name' => optional($module->padre)->nombre,
                    'permissions' => $module->permission->map(function($perm) {
                        // Usar friendly_name si existe, sino usar display_name del accessor
                        $displayName = $perm->friendly_name ?: $perm->display_name;

                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'friendly_name' => $perm->friendly_name,
                            'display_name' => $displayName,
                            'category' => $perm->category,
                        ];
                    })
                ];
            });

            return ApiResponse::success($grouped, 'Permisos agrupados recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener permisos agrupados', 500);
        }
    }

    /**
     * POST /api/v1/permissions/sync
     * Sincronizar permisos desde los módulos
     * Crea permisos automáticamente basándose en los módulos activos
     */
    public function syncFromModules(): JsonResponse
    {
        try {
            $modules = Modulo::where('is_padre', 0)->where('is_active', 1)->get();

            $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'deleted' => 0];

            foreach ($modules as $module) {
                $moduleName = str_replace(' ', '_', strtolower($module->nombre));

                // Determinar tipo de permisos según características del módulo
                $permissionTypes = $this->getPermissionTypesForModule($module);

                foreach ($permissionTypes as $type => $friendlyName) {
                    $permissionName = "{$moduleName}.{$type}";

                    $permission = Permission::where('name', $permissionName)
                        ->where('guard_name', 'api')
                        ->first();

                    if ($permission) {
                        // Actualizar si cambió el module_id
                        if ($permission->module_id != $module->id) {
                            $permission->update([
                                'module_id' => $module->id,
                                'category' => $this->getCategoryForModule($module),
                                'friendly_name' => $friendlyName,
                            ]);
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } else {
                        // Crear nuevo permiso
                        Permission::create([
                            'name' => $permissionName,
                            'guard_name' => 'api',
                            'module_id' => $module->id,
                            'category' => $this->getCategoryForModule($module),
                            'friendly_name' => $friendlyName,
                        ]);
                        $stats['created']++;
                    }
                }
            }

            // Eliminar permisos huérfanos (módulos que ya no existen)
            $existingModuleIds = $modules->pluck('id')->toArray();
            $deleted = Permission::whereNotNull('module_id')
                ->whereNotIn('module_id', $existingModuleIds)
                ->delete();

            $stats['deleted'] = $deleted;

            return ApiResponse::success($stats, 'Permisos sincronizados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al sincronizar permisos', 500);
        }
    }

    /**
     * Determinar tipos de permisos según el tipo de módulo
     */
    private function getPermissionTypesForModule($module): array
    {
        $routeLower = strtolower($module->ruta);

        // Módulos de reportes
        if (str_contains($routeLower, 'report') || str_contains($routeLower, 'reporte')) {
            return [
                'view' => 'Ver',
                'export' => 'Exportar',
                'generate' => 'Generar',
            ];
        }

        // Módulos transaccionales (ventas, compras)
        if (str_contains($routeLower, 'sales') || str_contains($routeLower, 'venta') ||
            str_contains($routeLower, 'purchase') || str_contains($routeLower, 'compra') ||
            str_contains($routeLower, '/new')) {
            return [
                'view' => 'Ver',
                'create' => 'Crear',
                'update' => 'Editar',
                'cancel' => 'Anular',
                'authorize' => 'Autorizar',
                'export' => 'Exportar',
            ];
        }

        // Módulo de roles (especial)
        if (str_contains($routeLower, 'setting/rol')) {
            return [
                'view' => 'Ver',
                'create' => 'Crear',
                'update' => 'Editar',
                'delete' => 'Eliminar',
                'manage_permissions' => 'Gestionar Permisos',
                'export' => 'Exportar',
                'bulk_activate' => 'Activar en Lote',
                'bulk_deactivate' => 'Desactivar en Lote',
                'bulk_delete' => 'Eliminar en Lote',
            ];
        }

        // Módulos de configuración
        if (str_contains($routeLower, 'setting') || str_contains($routeLower, 'config')) {
            return [
                'view' => 'Ver',
                'create' => 'Crear',
                'update' => 'Editar',
                'delete' => 'Eliminar',
                'export' => 'Exportar',
                'bulk_activate' => 'Activar en Lote',
                'bulk_deactivate' => 'Desactivar en Lote',
                'bulk_delete' => 'Eliminar en Lote',
            ];
        }

        // Por defecto: módulos de catálogo (CRUD básico)
        return [
            'view' => 'Ver',
            'create' => 'Crear',
            'update' => 'Editar',
            'delete' => 'Eliminar',
            'export' => 'Exportar',
            'bulk_activate' => 'Activar en Lote',
            'bulk_deactivate' => 'Desactivar en Lote',
            'bulk_delete' => 'Eliminar en Lote',
        ];
    }

    /**
     * Determinar categoría del permiso según el tipo de módulo
     */
    private function getCategoryForModule($module): string
    {
        $routeLower = strtolower($module->ruta);

        if (str_contains($routeLower, 'report')) return 'report';
        if (str_contains($routeLower, 'sales') || str_contains($routeLower, 'purchase')) return 'transaction';
        if (str_contains($routeLower, 'setting')) return 'configuration';

        return 'crud';
    }
}
