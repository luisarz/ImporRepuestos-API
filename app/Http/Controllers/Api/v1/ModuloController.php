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
use Illuminate\Support\Facades\Log;

class ModuloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 100);
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'orden');
            $sortOrder = $request->input('desc', 'asc');
            $statusFilter = $request->input('status_filter', '');
            $validSortFields = ['nombre', 'ruta', 'orden', 'is_active']; // Add your actual columns here
            $sortField = in_array($sortField, $validSortFields) ? $sortField : 'orden';
            $query = Modulo::query();
            $query->with('padre:id,nombre,ruta,icono,orden');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ruta', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            $results = $query->orderBy($sortField, $sortOrder)
                ->paginate($perPage);

            return ApiResponse::success($results, 'Módulos recuperados exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function getAll(){
        try {
            $modulos = Modulo::with('padre')->get();
            return ApiResponse::success($modulos, 'Módulos recuperados exitosamente',200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }
    }

    public function store(ModuloStoreRequest $request): JsonResponse
    {
        try {

            $modulo = Modulo::create($request->validated());
            //Crear todos los permisos para el modulo
            $module_name = str_replace(' ', '_', strtolower($modulo->nombre));
            $is_parent = $request->is_padre;

            if(!$is_parent){
                // Determinar tipos de permisos según el tipo de módulo
                $permissionTypes = $this->getPermissionTypesForModule($modulo);
                $category = $this->getCategoryForModule($modulo);

                $createdPermissions = [];
                foreach ($permissionTypes as $type => $friendlyName) {
                    $permissionName = "{$module_name}.{$type}";

                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'api',
                        'module_id' => $modulo->id,
                        'category' => $category,
                        'friendly_name' => $friendlyName,
                    ]);

                    $createdPermissions[] = $permission;
                }

                $response=[
                    'module'=>$modulo,
                    'permissions'=>$createdPermissions
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

    public function update(ModuloStoreRequest $request,$id): JsonResponse
    {
        try {
            $modulo=Modulo::findOrFail($id);
            $modulo->update($request->validated());
            $module_name = str_replace(' ', '_', strtolower($modulo->nombre));
            $is_parent = $request->is_padre;

            if (!$is_parent) {
                // Determinar tipos de permisos según el tipo de módulo
                $permissionTypes = $this->getPermissionTypesForModule($modulo);
                $category = $this->getCategoryForModule($modulo);

                // Eliminar los permisos antiguos asociados al módulo
                Permission::where('module_id',$id)->delete();

                // Crear los nuevos permisos con formato correcto y friendly_name
                $createdPermissions = [];
                foreach ($permissionTypes as $type => $friendlyName) {
                    $permissionName = "{$module_name}.{$type}";

                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'api',
                        'module_id' => $modulo->id,
                        'category' => $category,
                        'friendly_name' => $friendlyName,
                    ]);

                    $createdPermissions[] = $permission;
                }

                $response = [
                    'module' => $modulo,
                    'permissions' => $createdPermissions
                ];
                return ApiResponse::success($response, 'Módulo actualizado exitosamente con sus permisos', 200);
            }

            return ApiResponse::success($modulo, 'Módulo actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Módulo no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }


    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $modulo=Modulo::findOrFail($id);
            $modulo->delete();
           return ApiResponse::success(null,'Modulo eliminado exitosamente',200);

        }catch (ModelNotFoundException $e){
            return ApiResponse::error(null,'Modulo no encontrado',404);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(), 500);
        }

    }

    /**
     * Obtener estadísticas de módulos
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Modulo::count();
            $active = Modulo::where('is_active', 1)->count();
            $inactive = Modulo::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Obtener módulos por IDs
     */
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $modulos = Modulo::whereIn('id', $ids)->get();

            return ApiResponse::success($modulos, 'Módulos recuperados exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Activar múltiples módulos
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Modulo::whereIn('id', $ids)->update(['is_active' => 1]);

            return ApiResponse::success(null, 'Módulos activados exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Desactivar múltiples módulos
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Modulo::whereIn('id', $ids)->update(['is_active' => 0]);

            return ApiResponse::success(null, 'Módulos desactivados exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar múltiples módulos
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Modulo::whereIn('id', $ids)->delete();

            return ApiResponse::success(null, 'Módulos eliminados exitosamente', 200);

        } catch (\Exception $e) {
            return ApiResponse::error(null, $e->getMessage(), 500);
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
