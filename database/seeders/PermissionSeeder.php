<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Deshabilitar verificación de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar tablas existentes
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        // Habilitar verificación de foreign keys nuevamente
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Definir acciones estándar para cada módulo
        $actions = ['view', 'create', 'edit', 'delete'];

        // Módulos que solo requieren 'view' (son solo de visualización o navegación)
        $viewOnlyModules = [
            1,  // Dashboard
            2,  // Configuración (parent)
            10, // Catálogos (parent)
            20, // Catálogos Hacienda (parent)
            40, // Proveedores (parent)
            50, // Clientes (parent)
            70, // Productos (parent)
            80, // Inventario (parent)
            90, // Parque Vehicular (parent)
            100, // Compras (parent)
            110, // Ventas (parent)
            120, // Facturación Electrónica (parent)
            130, // Reportes
        ];

        // Módulos que requieren acciones especiales
        $specialModules = [
            112 => ['view', 'create'], // Nueva Venta (no edit/delete)
            121 => ['view', 'download', 'resend'], // Historial DTEs
            122 => ['view', 'create', 'resolve'], // Contingencias
            81 => ['view', 'export'], // Inventario General
            82 => ['view', 'export'], // Inventarios por Lote
            83 => ['view', 'export'], // Historial de Costos
            84 => ['view', 'export'], // Kardex
        ];

        // Obtener todos los módulos de la base de datos con información del padre
        $modulos = DB::table('modulo as m')
            ->leftJoin('modulo as p', 'm.id_padre', '=', 'p.id')
            ->select('m.*', 'p.nombre as nombre_padre')
            ->get();

        $permissions = [];
        $usedPermissionNames = [];

        foreach ($modulos as $modulo) {
            // Determinar qué acciones aplicar
            if (isset($specialModules[$modulo->id])) {
                $moduleActions = $specialModules[$modulo->id];
            } elseif (in_array($modulo->id, $viewOnlyModules)) {
                $moduleActions = ['view'];
            } else {
                $moduleActions = $actions;
            }

            // Crear permisos para cada acción
            foreach ($moduleActions as $action) {
                // Generar nombre base del permiso
                $baseName = $this->normalizeModuleName($modulo->nombre);
                $permissionName = $action . '_' . $baseName;

                // Si el nombre ya está en uso y el módulo tiene padre, agregar contexto del padre
                if (isset($usedPermissionNames[$permissionName]) && $modulo->nombre_padre) {
                    $parentContext = $this->normalizeModuleName($modulo->nombre_padre);
                    $permissionName = $action . '_' . $baseName . '_' . $parentContext;
                }

                // Marcar nombre como usado
                $usedPermissionNames[$permissionName] = true;

                $permissions[] = [
                    'name' => $permissionName,
                    'guard_name' => 'api',
                    'module_id' => $modulo->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertar permisos en la base de datos
        foreach (array_chunk($permissions, 50) as $chunk) {
            Permission::insert($chunk);
        }

        $this->command->info('✅ ' . count($permissions) . ' permisos creados exitosamente');

        // Crear rol de Super Admin con todos los permisos
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'api'],
            ['description' => 'Acceso total al sistema']
        );

        $allPermissions = Permission::where('guard_name', 'api')->pluck('name');
        $superAdmin->syncPermissions($allPermissions);

        $this->command->info('✅ Rol Super Admin creado con ' . $allPermissions->count() . ' permisos');

        // Crear rol de Administrador con la mayoría de permisos
        $admin = Role::firstOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'api'],
            ['description' => 'Gestión completa excepto configuraciones críticas']
        );

        $adminPermissions = Permission::where('guard_name', 'api')
            ->whereNotIn('name', [
                'delete_empresa',
                'delete_usuarios',
                'delete_roles',
                'edit_configuración',
            ])
            ->pluck('name');

        $admin->syncPermissions($adminPermissions);

        $this->command->info('✅ Rol Administrador creado con ' . $adminPermissions->count() . ' permisos');

        // Crear rol de Vendedor
        $vendedor = Role::firstOrCreate(
            ['name' => 'Vendedor', 'guard_name' => 'api'],
            ['description' => 'Gestión de ventas y clientes']
        );

        // Obtener permisos existentes que coincidan con patrones
        $vendedorPermissionPatterns = [
            'view_dashboard',
            'view_ventas',
            'view_lista_de_ventas',
            'create_lista_de_ventas',
            'edit_lista_de_ventas',
            'view_nueva_venta',
            'create_nueva_venta',
            'view_detalles_de_pago',
            'view_clientes',
            'view_lista_de_clientes',
            'create_lista_de_clientes',
            'edit_lista_de_clientes',
            'view_productos',
            'view_lista_de_productos',
            'view_inventario',
            'view_inventario_general',
        ];

        // Agregar permisos que contengan estas palabras clave
        $vendedorKeywords = ['direcciones.*clientes', 'historial.*dtes'];

        $vendedorPermissions = Permission::where('guard_name', 'api')
            ->where(function($query) use ($vendedorPermissionPatterns, $vendedorKeywords) {
                $query->whereIn('name', $vendedorPermissionPatterns);
                foreach ($vendedorKeywords as $keyword) {
                    $query->orWhere('name', 'REGEXP', $keyword);
                }
            })
            ->pluck('name')
            ->toArray();

        $vendedor->syncPermissions($vendedorPermissions);

        $this->command->info('✅ Rol Vendedor creado con ' . count($vendedorPermissions) . ' permisos');

        // Crear rol de Almacenista
        $almacenista = Role::firstOrCreate(
            ['name' => 'Almacenista', 'guard_name' => 'api'],
            ['description' => 'Gestión de inventarios y almacenes']
        );

        $almacenistaPermissions = Permission::where('guard_name', 'api')
            ->where(function($query) {
                $query->where('name', 'view_dashboard')
                    ->orWhere('name', 'LIKE', '%inventario%')
                    ->orWhere('name', 'LIKE', '%kardex%')
                    ->orWhere('name', 'LIKE', '%historial%cost%')
                    ->orWhere('name', 'LIKE', '%producto%')
                    ->orWhere('name', 'LIKE', '%lote%')
                    ->orWhere('name', 'LIKE', '%almacen%')
                    ->orWhere('name', 'LIKE', '%compra%');
            })
            ->whereNotIn('name', ['delete_almacenes', 'delete_productos']) // Almacenista no puede eliminar
            ->pluck('name')
            ->toArray();

        $almacenista->syncPermissions($almacenistaPermissions);

        $this->command->info('✅ Rol Almacenista creado con ' . count($almacenistaPermissions) . ' permisos');

        // Crear rol de Contador
        $contador = Role::firstOrCreate(
            ['name' => 'Contador', 'guard_name' => 'api'],
            ['description' => 'Acceso a reportes y facturación']
        );

        $contadorPermissions = Permission::where('guard_name', 'api')
            ->where(function($query) {
                $query->where('name', 'view_dashboard')
                    ->orWhere('name', 'LIKE', '%reporte%')
                    ->orWhere('name', 'LIKE', '%facturacion%')
                    ->orWhere('name', 'LIKE', '%dte%')
                    ->orWhere('name', 'LIKE', '%contingencia%')
                    ->orWhere('name', 'LIKE', 'view_venta%')
                    ->orWhere('name', 'LIKE', 'view_compra%')
                    ->orWhere('name', 'LIKE', 'view_cliente%')
                    ->orWhere('name', 'LIKE', 'view_proveedor%');
            })
            ->pluck('name')
            ->toArray();

        $contador->syncPermissions($contadorPermissions);

        $this->command->info('✅ Rol Contador creado con ' . count($contadorPermissions) . ' permisos');

        $this->command->info('');
        $this->command->info('🎉 Todos los permisos y roles han sido creados exitosamente');
    }

    /**
     * Normalizar nombre de módulo para crear nombre de permiso
     */
    private function normalizeModuleName(string $name): string
    {
        // Convertir a minúsculas
        $name = mb_strtolower($name);

        // Reemplazar espacios y caracteres especiales
        $name = str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], ['_', 'a', 'e', 'i', 'o', 'u', 'n'], $name);

        // Remover caracteres no permitidos
        $name = preg_replace('/[^a-z0-9_]/', '', $name);

        return $name;
    }
}
