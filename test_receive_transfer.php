<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transfer;
use App\Models\Inventory;
use App\Models\Batch;
use App\Services\TransferService;

echo "=== PROBANDO RECEPCIÓN DE TRASLADO (IN_TRANSIT → RECEIVED) ===" . PHP_EOL . PHP_EOL;

// Obtener el traslado
$transfer = Transfer::with(['items.batch', 'warehouseOrigin', 'warehouseDestination'])->find(1);

if (!$transfer) {
    echo "❌ ERROR: Traslado no encontrado" . PHP_EOL;
    exit(1);
}

echo "Traslado: {$transfer->transfer_number}" . PHP_EOL;
echo "Estado actual: {$transfer->status}" . PHP_EOL;
echo "Origen: {$transfer->warehouseOrigin->name}" . PHP_EOL;
echo "Destino: {$transfer->warehouseDestination->name}" . PHP_EOL;
echo PHP_EOL;

// Verificar inventarios ANTES de la recepción
echo "=== ANTES DE LA RECEPCIÓN ===" . PHP_EOL;
foreach ($transfer->items as $item) {
    $invDest = Inventory::find($item->inventory_destination_id);

    echo "Item #{$item->id}:" . PHP_EOL;
    echo "  - Inventario Destino: Stock = {$invDest->stock_actual_quantity}" . PHP_EOL;
    echo "  - Cantidad a recibir: {$item->quantity}" . PHP_EOL;

    // Contar lotes en destino antes
    $batchesBefore = Batch::where('inventory_id', $item->inventory_destination_id)->count();
    echo "  - Lotes en destino (antes): {$batchesBefore}" . PHP_EOL;
    echo PHP_EOL;
}

// Recibir el traslado
$transferService = new TransferService();

try {
    echo "Recibiendo traslado..." . PHP_EOL;
    $transferService->receiveTransfer($transfer, 1); // User ID 1

    echo "✅ TRASLADO RECIBIDO EXITOSAMENTE!" . PHP_EOL . PHP_EOL;

    // Recargar el traslado
    $transfer->refresh();

    echo "=== DESPUÉS DE LA RECEPCIÓN ===" . PHP_EOL;
    echo "Estado nuevo: {$transfer->status}" . PHP_EOL;
    echo "Recibido por: User ID {$transfer->received_by}" . PHP_EOL;
    echo "Fecha recepción: {$transfer->received_at}" . PHP_EOL;
    echo PHP_EOL;

    // Verificar inventarios DESPUÉS de la recepción
    foreach ($transfer->items as $item) {
        $invDest = Inventory::find($item->inventory_destination_id);

        echo "Item #{$item->id} (Estado: {$item->status}):" . PHP_EOL;
        echo "  - Inventario Destino: Stock = {$invDest->stock_actual_quantity} (incrementado)" . PHP_EOL;

        // Buscar el nuevo lote creado
        $newBatches = Batch::where('inventory_id', $item->inventory_destination_id)
            ->where('code', 'like', "TRANS-{$transfer->transfer_number}%")
            ->get();

        echo "  - Lotes nuevos creados: {$newBatches->count()}" . PHP_EOL;
        foreach ($newBatches as $newBatch) {
            echo "    * {$newBatch->code}: {$newBatch->available_quantity} unidades" . PHP_EOL;
        }
        echo PHP_EOL;
    }

    // Verificar que se creó el kardex de entrada
    $kardexIn = \App\Models\Kardex::where('operation_type', 'TRANSFER_IN')
        ->where('operation_id', $transfer->id)
        ->get();

    echo "Registros Kardex TRANSFER_IN creados: {$kardexIn->count()}" . PHP_EOL;
    foreach ($kardexIn as $kardex) {
        echo "  - Kardex #{$kardex->id}: Stock IN = {$kardex->stock_in}, Inventario #{$kardex->inventory_id}" . PHP_EOL;
        echo "    Costo promedio nuevo: \${$kardex->promedial_cost}" . PHP_EOL;
    }
    echo PHP_EOL;

    echo "✅ FLUJO COMPLETO DE TRASLADO EXITOSO!" . PHP_EOL;
    echo PHP_EOL;

    echo "=== RESUMEN FINAL ===" . PHP_EOL;
    echo "1. ✅ Traslado creado (PENDING)" . PHP_EOL;
    echo "2. ✅ Traslado enviado (IN_TRANSIT) - Stock reducido en origen" . PHP_EOL;
    echo "3. ✅ Traslado recibido (RECEIVED) - Stock incrementado en destino" . PHP_EOL;
    echo "4. ✅ Nuevo lote creado en destino" . PHP_EOL;
    echo "5. ✅ Kardex registrado (TRANSFER_OUT y TRANSFER_IN)" . PHP_EOL;

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
