<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Batch;
use App\Models\PurchaseItem;

echo "=== CREANDO DATOS DE PRUEBA PARA TRASLADO ===" . PHP_EOL . PHP_EOL;

// Obtener almacenes
$warehouses = Warehouse::take(2)->get();

echo "✓ Almacén Origen: {$warehouses[0]->name} (ID: {$warehouses[0]->id})" . PHP_EOL;
echo "✓ Almacén Destino: {$warehouses[1]->name} (ID: {$warehouses[1]->id})" . PHP_EOL;
echo PHP_EOL;

// Buscar un producto que exista en ambos almacenes
$productsInW1 = Inventory::where('warehouse_id', $warehouses[0]->id)
    ->where('stock_actual_quantity', '>', 0)
    ->pluck('product_id');

$productsInW2 = Inventory::where('warehouse_id', $warehouses[1]->id)
    ->pluck('product_id');

$commonProducts = $productsInW1->intersect($productsInW2);
$productId = $commonProducts->first();
$product = Product::find($productId);

$invOrigin = Inventory::where('warehouse_id', $warehouses[0]->id)
    ->where('product_id', $productId)
    ->first();

$invDest = Inventory::where('warehouse_id', $warehouses[1]->id)
    ->where('product_id', $productId)
    ->first();

echo "Producto seleccionado: {$product->code} - {$product->name}" . PHP_EOL;
echo "Inventario Origen ID: {$invOrigin->id} - Stock: {$invOrigin->stock_actual_quantity}" . PHP_EOL;
echo "Inventario Destino ID: {$invDest->id} - Stock: {$invDest->stock_actual_quantity}" . PHP_EOL;
echo PHP_EOL;

// Verificar si hay lotes disponibles
$existingBatches = Batch::where('inventory_id', $invOrigin->id)
    ->where('is_active', true)
    ->where('available_quantity', '>', 0)
    ->get();

if ($existingBatches->count() === 0) {
    echo "No hay lotes disponibles. Creando lote de prueba..." . PHP_EOL;

    // Obtener un purchase_item o crear uno ficticio
    $purchaseItem = PurchaseItem::first();

    // Crear un lote de prueba
    $batch = Batch::create([
        'code' => 'TEST-LOTE-' . date('YmdHis'),
        'purchase_item_id' => $purchaseItem ? $purchaseItem->id : null,
        'origen_code' => 1, // Compra
        'inventory_id' => $invOrigin->id,
        'incoming_date' => now(),
        'expiration_date' => now()->addYear(),
        'initial_quantity' => 50,
        'available_quantity' => 50,
        'observations' => 'Lote de prueba para traslados',
        'is_active' => true
    ]);

    echo "✓ Lote creado: {$batch->code} (ID: {$batch->id}) - Cantidad: {$batch->available_quantity}" . PHP_EOL;

    // También crear la relación en inventories_batches
    \App\Models\InventoriesBatch::create([
        'id_inventory' => $invOrigin->id,
        'id_batch' => $batch->id,
        'quantity' => 50,
        'operation_date' => now()
    ]);

    echo "✓ Relación inventories_batches creada" . PHP_EOL;
    echo PHP_EOL;
} else {
    $batch = $existingBatches->first();
    echo "✓ Usando lote existente: {$batch->code} (ID: {$batch->id})" . PHP_EOL;
    echo PHP_EOL;
}

// Preparar el payload para crear el traslado
$quantityToTransfer = min(5, $batch->available_quantity);
$unitCost = $batch->purchaseItem ? floatval($batch->purchaseItem->price) : 10.00;

$payload = [
    'transfer_date' => date('Y-m-d'),
    'warehouse_origin_id' => $warehouses[0]->id,
    'warehouse_destination_id' => $warehouses[1]->id,
    'observations' => 'Traslado de prueba - Script automático',
    'items' => [
        [
            'product_id' => $product->id,
            'inventory_origin_id' => $invOrigin->id,
            'inventory_destination_id' => $invDest->id,
            'batch_id' => $batch->id,
            'quantity' => $quantityToTransfer,
            'unit_cost' => $unitCost
        ]
    ]
];

echo "=== CREANDO TRASLADO VIA API ===" . PHP_EOL;
echo "Payload:" . PHP_EOL;
echo json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL;
echo PHP_EOL;

// Crear el traslado usando el servicio directamente
DB::beginTransaction();

try {
    $transfer = \App\Models\Transfer::create([
        'transfer_date' => $payload['transfer_date'],
        'warehouse_origin_id' => $payload['warehouse_origin_id'],
        'warehouse_destination_id' => $payload['warehouse_destination_id'],
        'observations' => $payload['observations'],
        'status' => 'PENDING',
    ]);

    foreach ($payload['items'] as $itemData) {
        \App\Models\TransferItem::create([
            'transfer_id' => $transfer->id,
            'product_id' => $itemData['product_id'],
            'inventory_origin_id' => $itemData['inventory_origin_id'],
            'inventory_destination_id' => $itemData['inventory_destination_id'],
            'batch_id' => $itemData['batch_id'],
            'quantity' => $itemData['quantity'],
            'unit_cost' => $itemData['unit_cost'],
            'status' => 'PENDING',
        ]);
    }

    DB::commit();

    echo "✅ TRASLADO CREADO EXITOSAMENTE!" . PHP_EOL;
    echo "Número de traslado: {$transfer->transfer_number}" . PHP_EOL;
    echo "ID: {$transfer->id}" . PHP_EOL;
    echo "Estado: {$transfer->status}" . PHP_EOL;
    echo PHP_EOL;

    echo "=== SIGUIENTE PASO ===" . PHP_EOL;
    echo "Ahora puedes:" . PHP_EOL;
    echo "1. Ver el traslado en http://localhost:5175/transfers" . PHP_EOL;
    echo "2. Hacer clic en 'Enviar Traslado' para cambiar a IN_TRANSIT" . PHP_EOL;
    echo "3. Hacer clic en 'Recibir Traslado' para completar el proceso" . PHP_EOL;
    echo PHP_EOL;
    echo "O ejecutar los siguientes comandos:" . PHP_EOL;
    echo "php artisan tinker --execute=\"\App\Services\TransferService::class->sendTransfer(\App\Models\Transfer::find({$transfer->id}), 1)\"" . PHP_EOL;

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
