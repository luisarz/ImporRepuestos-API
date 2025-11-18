<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transfer;

echo "=== PROBANDO IMPRESIÓN DE TRASLADO ===\n\n";

// Obtener el primer traslado disponible
$transfer = Transfer::with([
    'warehouseOrigin',
    'warehouseDestination',
    'items.product',
    'items.batch'
])->first();

if (!$transfer) {
    echo "❌ ERROR: No hay traslados en la base de datos\n";
    echo "Ejecuta primero create_test_transfer.php para crear un traslado de prueba\n";
    exit(1);
}

echo "Traslado encontrado:\n";
echo "  ID: {$transfer->id}\n";
echo "  Número: {$transfer->transfer_number}\n";
echo "  Estado: {$transfer->status}\n";
echo "  Origen: {$transfer->warehouseOrigin->name}\n";
echo "  Destino: {$transfer->warehouseDestination->name}\n";
echo "  Items: {$transfer->items->count()}\n";
echo "\n";

// Calcular total
$total = $transfer->items->sum(function($item) {
    return $item->quantity * $item->unit_cost;
});

echo "  Total: $" . number_format($total, 2) . "\n";
echo "\n";

echo "Para probar la impresión, abre en tu navegador:\n";
echo "  http://127.0.0.1:8000/api/v1/transfers/{$transfer->id}/print\n";
echo "\n";
echo "O desde el frontend, ve a:\n";
echo "  http://localhost:5173/transfers/{$transfer->id}\n";
echo "  y haz clic en el botón 'Imprimir'\n";
echo "\n";
