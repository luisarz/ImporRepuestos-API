<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE CORRELATIVOS PARA CAJA 1 ===" . PHP_EOL . PHP_EOL;

$correlatives = \App\Models\Correlative::where('cash_register_id', 1)
    ->with('documentType')
    ->get();

echo "Total de correlativos: " . $correlatives->count() . PHP_EOL;
echo "Correlativos activos: " . $correlatives->where('is_active', 1)->count() . PHP_EOL . PHP_EOL;

if ($correlatives->count() === 0) {
    echo "❌ NO HAY CORRELATIVOS CONFIGURADOS PARA LA CAJA 1" . PHP_EOL;
} else {
    echo "Detalle de correlativos:" . PHP_EOL;
    echo str_repeat("-", 80) . PHP_EOL;

    foreach ($correlatives as $c) {
        echo sprintf(
            "ID: %d | Activo: %s | Tipo Doc: %s (ID: %s)" . PHP_EOL,
            $c->id,
            $c->is_active ? '✅ SÍ' : '❌ NO',
            $c->documentType ? $c->documentType->name : 'NULL',
            $c->documentType ? $c->documentType->id : 'NULL'
        );
    }
}

echo PHP_EOL . "=== FIN VERIFICACIÓN ===" . PHP_EOL;
