<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;

echo "=== PROBANDO IMPRESIÓN DE LOTE ===\n\n";

// Obtener el primer lote disponible
$batch = Batch::with(['inventory.product', 'inventory.warehouse', 'origenCode'])->first();

if (!$batch) {
    echo "❌ ERROR: No hay lotes en la base de datos\n";
    exit(1);
}

echo "Lote encontrado:\n";
echo "  ID: {$batch->id}\n";
echo "  Código: {$batch->code}\n";
echo "  Producto: " . ($batch->inventory->product->description ?? 'N/A') . "\n";
echo "  Almacén: " . ($batch->inventory->warehouse->name ?? 'N/A') . "\n";
echo "  Cantidad disponible: {$batch->available_quantity}\n";
echo "\n";

echo "Para probar la impresión, abre en tu navegador:\n";
echo "  http://127.0.0.1:8000/api/v1/batches/{$batch->id}/print\n";
echo "\n";
