<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Modulo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "\n";
echo "============================================\n";
echo "  VERIFICACIÓN DEL SISTEMA DE PERMISOS\n";
echo "============================================\n\n";

// Contar módulos
$totalModulos = Modulo::count();
$modulosPadre = Modulo::where('is_padre', true)->count();
$modulosHijo = Modulo::where('is_padre', false)->count();

echo "📦 MÓDULOS:\n";
echo "   Total: {$totalModulos}\n";
echo "   Padres: {$modulosPadre}\n";
echo "   Hijos: {$modulosHijo}\n\n";

// Listar módulos padre
echo "📋 Módulos Padre:\n";
$padres = Modulo::where('is_padre', true)->orderBy('orden')->get(['id', 'nombre']);
foreach ($padres as $padre) {
    $hijos = Modulo::where('id_padre', $padre->id)->count();
    echo "   {$padre->id}. {$padre->nombre} ({$hijos} hijos)\n";
}
echo "\n";

// Contar permisos
$totalPermisos = Permission::where('guard_name', 'api')->count();
echo "🔑 PERMISOS:\n";
echo "   Total: {$totalPermisos}\n\n";

// Permisos por módulo padre (muestra)
echo "📊 Permisos por categoría (muestra):\n";
$ventasPermisos = Permission::where('name', 'LIKE', '%venta%')->count();
$clientesPermisos = Permission::where('name', 'LIKE', '%cliente%')->count();
$productosPermisos = Permission::where('name', 'LIKE', '%producto%')->count();
$inventarioPermisos = Permission::where('name', 'LIKE', '%inventario%')->count();
echo "   Ventas: {$ventasPermisos}\n";
echo "   Clientes: {$clientesPermisos}\n";
echo "   Productos: {$productosPermisos}\n";
echo "   Inventario: {$inventarioPermisos}\n\n";

// Contar roles
$totalRoles = Role::where('guard_name', 'api')->count();
echo "👥 ROLES:\n";
echo "   Total: {$totalRoles}\n\n";

// Detalles de roles
$roles = Role::withCount('permissions')->get();
foreach ($roles as $role) {
    echo "   • {$role->name}: {$role->permissions_count} permisos\n";
    echo "     {$role->description}\n";
}
echo "\n";

// Usuario de prueba
$testUser = User::where('email', 'test@example.com')->first();
if ($testUser) {
    echo "👤 USUARIO DE PRUEBA:\n";
    echo "   Email: test@example.com\n";
    echo "   Password: password\n";
    echo "   Roles: " . $testUser->roles->pluck('name')->implode(', ') . "\n";
    echo "   Permisos totales: " . $testUser->getAllPermissions()->count() . "\n";
} else {
    echo "⚠️  Usuario de prueba no encontrado\n";
}

echo "\n";
echo "============================================\n";
echo "  ✅ VERIFICACIÓN COMPLETADA\n";
echo "============================================\n\n";
