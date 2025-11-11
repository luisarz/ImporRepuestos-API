<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders del sistema ImporRepuestos...');
        $this->command->info('');

        // 1. Módulos (deben existir antes de los permisos)
        $this->command->info('📦 Ejecutando ModuloSeeder...');
        $this->call(ModuloSeeder::class);
        $this->command->info('');

        // 2. Permisos y Roles (dependen de los módulos)
        $this->command->info('🔐 Ejecutando PermissionSeeder...');
        $this->call(PermissionSeeder::class);
        $this->command->info('');

        // 3. Usuario de prueba (opcional)
        $this->command->info('👤 Creando usuario de prueba...');

        // Crear usuario manualmente para evitar problemas con factories
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        // Asignar rol de Super Admin al usuario de prueba
        if (!$testUser->hasRole('Super Admin')) {
            $testUser->assignRole('Super Admin');
        }

        $this->command->info('✅ Usuario de prueba creado con rol Super Admin');
        $this->command->info('   Email: test@example.com');
        $this->command->info('   Password: password');
        $this->command->info('');

        $this->command->info('🎉 ¡Todos los seeders se ejecutaron exitosamente!');
    }
}
