<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "\n";
echo "============================================\n";
echo "  MUESTRA DE PERMISOS CREADOS\n";
echo "============================================\n\n";

// Mostrar permisos de Ventas
echo "📌 Permisos de VENTAS:\n";
$ventasPermisos = Permission::where('name', 'LIKE', '%venta%')->orderBy('name')->get(['name', 'module_id']);
foreach ($ventasPermisos as $perm) {
    echo "   • {$perm->name} (módulo #{$perm->module_id})\n";
}
echo "\n";

// Mostrar permisos de Inventario
echo "📌 Permisos de INVENTARIO:\n";
$inventarioPermisos = Permission::where('name', 'LIKE', '%inventario%')->orderBy('name')->get(['name', 'module_id']);
foreach ($inventarioPermisos as $perm) {
    echo "   • {$perm->name} (módulo #{$perm->module_id})\n";
}
echo "\n";

// Mostrar permisos del Vendedor
echo "📌 Permisos del ROL VENDEDOR:\n";
$vendedor = Role::findByName('Vendedor');
$vendedorPerms = $vendedor->permissions()->orderBy('name')->pluck('name');
foreach ($vendedorPerms as $perm) {
    echo "   • {$perm}\n";
}
echo "\n";

// Mostrar permisos especiales (direcciones)
echo "📌 Permisos de DIRECCIONES (con contexto):\n";
$direccionesPermisos = Permission::where('name', 'LIKE', '%direccion%')->orderBy('name')->get(['name', 'module_id']);
foreach ($direccionesPermisos as $perm) {
    echo "   • {$perm->name} (módulo #{$perm->module_id})\n";
}
echo "\n";

echo "============================================\n\n";
