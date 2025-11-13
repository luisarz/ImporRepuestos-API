<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE EMPRESA ===" . PHP_EOL . PHP_EOL;

$company = \App\Models\Company::find(1);

if ($company) {
    echo "✅ Empresa encontrada con ID 1:" . PHP_EOL;
    echo "Nombre: " . $company->name . PHP_EOL;
    echo "NIT: " . ($company->nit ?? 'N/A') . PHP_EOL;
    echo "API Key MH: " . ($company->api_key_mh ? 'Configurado' : 'NO configurado') . PHP_EOL;
} else {
    echo "❌ No existe empresa con ID 1" . PHP_EOL . PHP_EOL;
    echo "Empresas disponibles en la base de datos:" . PHP_EOL;
    echo str_repeat("-", 80) . PHP_EOL;

    $companies = \App\Models\Company::all();

    if ($companies->count() > 0) {
        foreach($companies as $c) {
            echo "ID: " . $c->id . " | Nombre: " . $c->name . PHP_EOL;
        }
    } else {
        echo "No hay empresas registradas en la base de datos." . PHP_EOL;
    }
}

echo PHP_EOL . "=== FIN VERIFICACIÓN ===" . PHP_EOL;
