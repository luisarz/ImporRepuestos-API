<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Batch;

echo "=== PREPARACIÓN PARA PRUEBA DE TRASLADOS ===" . PHP_EOL . PHP_EOL;

// Obtener almacenes
$warehouses = Warehouse::take(2)->get();

if ($warehouses->count() < 2) {
    echo "ERROR: Se necesitan al menos 2 almacenes en el sistema." . PHP_EOL;
    exit(1);
}

echo "✓ Almacén Origen: {$warehouses[0]->name} (ID: {$warehouses[0]->id})" . PHP_EOL;
echo "✓ Almacén Destino: {$warehouses[1]->name} (ID: {$warehouses[1]->id})" . PHP_EOL;
echo PHP_EOL;

// Buscar productos que existan en ambos almacenes
$productsInW1 = Inventory::where('warehouse_id', $warehouses[0]->id)
    ->where('stock_actual_quantity', '>', 0)
    ->pluck('product_id');

$productsInW2 = Inventory::where('warehouse_id', $warehouses[1]->id)
    ->pluck('product_id');

$commonProducts = $productsInW1->intersect($productsInW2);

echo "Productos en ambos almacenes: {$commonProducts->count()}" . PHP_EOL;

if ($commonProducts->count() === 0) {
    echo "ERROR: No hay productos que existan en ambos almacenes." . PHP_EOL;
    exit(1);
}

// Tomar el primer producto común
$productId = $commonProducts->first();
$product = Product::find($productId);

$invOrigin = Inventory::where('warehouse_id', $warehouses[0]->id)
    ->where('product_id', $productId)
    ->first();

$invDest = Inventory::where('warehouse_id', $warehouses[1]->id)
    ->where('product_id', $productId)
    ->first();

echo PHP_EOL . "--- PRODUCTO SELECCIONADO ---" . PHP_EOL;
echo "Producto: {$product->name}" . PHP_EOL;
echo "Código: {$product->code}" . PHP_EOL;
echo "Product ID: {$product->id}" . PHP_EOL;
echo PHP_EOL;

echo "Inventario Origen:" . PHP_EOL;
echo "  - ID: {$invOrigin->id}" . PHP_EOL;
echo "  - Stock actual: {$invOrigin->stock_actual_quantity}" . PHP_EOL;
echo PHP_EOL;

echo "Inventario Destino:" . PHP_EOL;
echo "  - ID: {$invDest->id}" . PHP_EOL;
echo "  - Stock actual: {$invDest->stock_actual_quantity}" . PHP_EOL;
echo PHP_EOL;

// Buscar lotes disponibles en el inventario origen
$batches = Batch::where('inventory_id', $invOrigin->id)
    ->where('is_active', true)
    ->where('available_quantity', '>', 0)
    ->get();

echo "Lotes disponibles en origen: {$batches->count()}" . PHP_EOL;

if ($batches->count() === 0) {
    echo "ERROR: No hay lotes disponibles para el producto seleccionado." . PHP_EOL;
    exit(1);
}

foreach ($batches as $batch) {
    echo "  - Lote ID: {$batch->id}" . PHP_EOL;
    echo "    Código: {$batch->code}" . PHP_EOL;
    echo "    Cantidad disponible: {$batch->available_quantity}" . PHP_EOL;
    if ($batch->purchaseItem) {
        echo "    Costo unitario: \${$batch->purchaseItem->price}" . PHP_EOL;
    }
    echo PHP_EOL;
}

// Preparar datos para el traslado
$batchToUse = $batches->first();
$quantityToTransfer = min(5, $batchToUse->available_quantity);

echo "=== DATOS PARA CREAR TRASLADO ===" . PHP_EOL . PHP_EOL;
echo "JSON Payload:" . PHP_EOL;

$payload = [
    'transfer_date' => date('Y-m-d'),
    'warehouse_origin_id' => $warehouses[0]->id,
    'warehouse_destination_id' => $warehouses[1]->id,
    'observations' => 'Traslado de prueba - Generado automáticamente',
    'items' => [
        [
            'product_id' => $product->id,
            'inventory_origin_id' => $invOrigin->id,
            'inventory_destination_id' => $invDest->id,
            'batch_id' => $batchToUse->id,
            'quantity' => $quantityToTransfer,
            'unit_cost' => $batchToUse->purchaseItem ? $batchToUse->purchaseItem->price : 10.00
        ]
    ]
];

echo json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL;
echo PHP_EOL;

echo "=== EJECUTAR COMANDO CURL ===" . PHP_EOL;
echo "Puedes usar este comando para crear el traslado:" . PHP_EOL . PHP_EOL;

$jsonPayload = json_encode($payload);
echo 'curl -X POST http://127.0.0.1:8000/api/v1/transfers \\' . PHP_EOL;
echo '  -H "Content-Type: application/json" \\' . PHP_EOL;
echo '  -H "Authorization: Bearer YOUR_TOKEN_HERE" \\' . PHP_EOL;
echo "  -d '" . $jsonPayload . "'" . PHP_EOL;
echo PHP_EOL;

echo "✓ Script completado exitosamente!" . PHP_EOL;
