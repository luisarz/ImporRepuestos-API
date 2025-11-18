<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CashClosurePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buscar el módulo de Aperturas de Caja
        $cashOpeningModule = DB::table('modulo')
            ->where('nombre', 'like', '%apertura%caja%')
            ->orWhere('nombre', 'like', '%cash%opening%')
            ->first();

        if (!$cashOpeningModule) {
            $this->command->warn('⚠️  No se encontró el módulo de Aperturas de Caja');
            $this->command->warn('   Creando permiso sin asociarlo a un módulo...');
        }

        // Crear el permiso de cierre de caja si no existe
        $closurePermission = Permission::firstOrCreate(
            [
                'name' => 'close_cash_opening',
                'guard_name' => 'api'
            ],
            [
                'module_id' => $cashOpeningModule->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($closurePermission->wasRecentlyCreated) {
            $this->command->info('✅ Permiso "close_cash_opening" creado exitosamente');
        } else {
            $this->command->info('ℹ️  Permiso "close_cash_opening" ya existe');
        }

        // Asignar el permiso al rol Super Admin
        $superAdmin = Role::where('name', 'Super Admin')
            ->where('guard_name', 'api')
            ->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo('close_cash_opening');
            $this->command->info('✅ Permiso asignado al rol Super Admin');
        }

        // Asignar el permiso al rol Administrador
        $admin = Role::where('name', 'Administrador')
            ->where('guard_name', 'api')
            ->first();

        if ($admin) {
            $admin->givePermissionTo('close_cash_opening');
            $this->command->info('✅ Permiso asignado al rol Administrador');
        }

        $this->command->info('');
        $this->command->info('🎉 Seeder completado. El permiso "close_cash_opening" está listo para usar.');
        $this->command->info('   Puedes asignar este permiso a otros roles desde el módulo de Roles y Permisos.');
    }
}
