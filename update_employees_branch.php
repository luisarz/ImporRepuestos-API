<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTUALIZACIÓN DE BRANCH_ID EN EMPLEADOS ===" . PHP_EOL . PHP_EOL;

// Actualizar todos los empleados que tengan branch_id null
$updated = \App\Models\Employee::whereNull('branch_id')->update(['branch_id' => 1]);

echo "✅ Empleados actualizados: " . $updated . PHP_EOL;
echo "   Todos los empleados ahora tienen branch_id = 1" . PHP_EOL . PHP_EOL;

// Verificar
$employees = \App\Models\Employee::all();
echo "Verificación:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
foreach ($employees as $emp) {
    echo "ID: " . $emp->id . " | Nombre: " . $emp->name . " | Branch ID: " . $emp->branch_id . PHP_EOL;
}

echo PHP_EOL . "=== FIN ACTUALIZACIÓN ===" . PHP_EOL;
