<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE USUARIOS Y EMPLEADOS ===" . PHP_EOL . PHP_EOL;

// Obtener todos los usuarios
$users = \App\Models\User::with('employee')->get();

echo "Total de usuarios: " . $users->count() . PHP_EOL . PHP_EOL;

foreach ($users as $user) {
    echo str_repeat("-", 80) . PHP_EOL;
    echo "Usuario ID: " . $user->id . PHP_EOL;
    echo "Nombre: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;

    if ($user->employee) {
        echo "✅ Tiene empleado asignado" . PHP_EOL;
        echo "   Employee ID: " . $user->employee->id . PHP_EOL;
        echo "   Nombre: " . $user->employee->name . " " . ($user->employee->last_name ?? '') . PHP_EOL;
        echo "   Branch ID: " . ($user->employee->branch_id ?? 'NULL ❌') . PHP_EOL;
    } else {
        echo "❌ NO tiene empleado asignado" . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "=== FIN VERIFICACIÓN ===" . PHP_EOL;
