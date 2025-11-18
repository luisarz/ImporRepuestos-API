<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transfer;
use App\Models\Inventory;
use App\Models\Batch;
use App\Services\TransferService;

echo "=== PROBANDO ENVÍO DE TRASLADO (PENDING → IN_TRANSIT) ===" . PHP_EOL . PHP_EOL;

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

// Verificar stock y lotes ANTES del envío
echo "=== ANTES DEL ENVÍO ===" . PHP_EOL;
foreach ($transfer->items as $item) {
    $invOrigin = Inventory::find($item->inventory_origin_id);
    $batch = Batch::find($item->batch_id);

    echo "Item #{$item->id}:" . PHP_EOL;
    echo "  - Inventario Origen: Stock = {$invOrigin->stock_actual_quantity}" . PHP_EOL;
    echo "  - Lote {$batch->code}: Disponible = {$batch->available_quantity}" . PHP_EOL;
    echo "  - Cantidad a trasladar: {$item->quantity}" . PHP_EOL;
    echo PHP_EOL;
}

// Enviar el traslado
$transferService = new TransferService();

try {
    echo "Enviando traslado..." . PHP_EOL;
    $transferService->sendTransfer($transfer, 1); // User ID 1

    echo "✅ TRASLADO ENVIADO EXITOSAMENTE!" . PHP_EOL . PHP_EOL;

    // Recargar el traslado
    $transfer->refresh();

    echo "=== DESPUÉS DEL ENVÍO ===" . PHP_EOL;
    echo "Estado nuevo: {$transfer->status}" . PHP_EOL;
    echo "Enviado por: User ID {$transfer->sent_by}" . PHP_EOL;
    echo "Fecha envío: {$transfer->sent_at}" . PHP_EOL;
    echo PHP_EOL;

    // Verificar stock y lotes DESPUÉS del envío
    foreach ($transfer->items as $item) {
        $invOrigin = Inventory::find($item->inventory_origin_id);
        $batch = Batch::find($item->batch_id);

        echo "Item #{$item->id} (Estado: {$item->status}):" . PHP_EOL;
        echo "  - Inventario Origen: Stock = {$invOrigin->stock_actual_quantity} (reducido)" . PHP_EOL;
        echo "  - Lote {$batch->code}: Disponible = {$batch->available_quantity} (reducido)" . PHP_EOL;
        echo PHP_EOL;
    }

    // Verificar que se creó el kardex
    $kardexRecords = \App\Models\Kardex::where('operation_type', 'TRANSFER_OUT')
        ->where('operation_id', $transfer->id)
        ->get();

    echo "Registros Kardex creados: {$kardexRecords->count()}" . PHP_EOL;
    foreach ($kardexRecords as $kardex) {
        echo "  - Kardex #{$kardex->id}: Stock OUT = {$kardex->stock_out}, Inventario #{$kardex->inventory_id}" . PHP_EOL;
    }
    echo PHP_EOL;

    echo "✅ El traslado está listo para ser recibido en el almacén destino" . PHP_EOL;

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
