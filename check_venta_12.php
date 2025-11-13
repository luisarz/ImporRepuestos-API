<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE VENTA ID 12 ===" . PHP_EOL . PHP_EOL;

$venta = \App\Models\SalesHeader::with('saleCondition')->find(12);

if (!$venta) {
    echo "❌ Venta ID 12 no encontrada" . PHP_EOL;
    exit;
}

echo "Información de la venta:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
echo "ID: " . $venta->id . PHP_EOL;
echo "operation_condition_id: " . ($venta->operation_condition_id ?? 'NULL') . PHP_EOL;
echo "payment_method_id: " . ($venta->payment_method_id ?? 'NULL') . PHP_EOL;
echo "payment_status: " . ($venta->payment_status ?? 'NULL') . PHP_EOL;
echo "document_type_id: " . ($venta->document_type_id ?? 'NULL') . PHP_EOL;
echo PHP_EOL;

echo "Relación saleCondition:" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;
if ($venta->saleCondition) {
    echo "✅ SaleCondition CARGADA" . PHP_EOL;
    echo "ID: " . $venta->saleCondition->id . PHP_EOL;
    echo "Code: " . $venta->saleCondition->code . PHP_EOL;
    echo "Name: " . $venta->saleCondition->name . PHP_EOL;
} else {
    echo "❌ SaleCondition es NULL" . PHP_EOL;
}

echo PHP_EOL . "=== FIN VERIFICACIÓN ===" . PHP_EOL;
