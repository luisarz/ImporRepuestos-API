<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\TransferStoreRequest;
use App\Models\Inventory;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Services\TransferService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Listar todos los traslados
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            $warehouseOriginId = $request->input('warehouse_origin_id', '');
            $warehouseDestinationId = $request->input('warehouse_destination_id', '');

            $query = Transfer::with([
                'warehouseOrigin',
                'warehouseDestination',
                'sentByUser',
                'receivedByUser',
                'items.product'
            ]);

            // Búsqueda por número de traslado
            if (!empty($search)) {
                $query->where('transfer_number', 'like', "%{$search}%");
            }

            // Filtro por status
            if (!empty($status)) {
                $query->where('status', $status);
            }

            // Filtro por warehouse origen
            if (!empty($warehouseOriginId)) {
                $query->where('warehouse_origin_id', $warehouseOriginId);
            }

            // Filtro por warehouse destino
            if (!empty($warehouseDestinationId)) {
                $query->where('warehouse_destination_id', $warehouseDestinationId);
            }

            $transfers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success($transfers, 'Traslados recuperados con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Crear un nuevo traslado
     */
    public function store(TransferStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Crear el traslado
            $transfer = Transfer::create([
                'transfer_date' => $request->transfer_date,
                'warehouse_origin_id' => $request->warehouse_origin_id,
                'warehouse_destination_id' => $request->warehouse_destination_id,
                'observations' => $request->observations,
                'status' => 'PENDING',
            ]);

            // Crear los items del traslado
            foreach ($request->items as $itemData) {
                TransferItem::create([
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

            // Cargar relaciones para la respuesta
            $transfer->load([
                'warehouseOrigin',
                'warehouseDestination',
                'items.product',
                'items.batch'
            ]);

            return ApiResponse::success($transfer, 'Traslado creado exitosamente', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al crear el traslado', 500);
        }
    }

    /**
     * Mostrar detalle de un traslado
     */
    public function show($id): JsonResponse
    {
        try {
            $transfer = Transfer::with([
                'warehouseOrigin',
                'warehouseDestination',
                'sentByUser',
                'receivedByUser',
                'items.product',
                'items.batch',
                'items.inventoryOrigin',
                'items.inventoryDestination'
            ])->findOrFail($id);

            return ApiResponse::success($transfer, 'Traslado recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Traslado no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Enviar/despachar un traslado (cambiar a IN_TRANSIT)
     */
    public function send($id): JsonResponse
    {
        try {
            $transfer = Transfer::with('items')->findOrFail($id);

            $this->transferService->sendTransfer($transfer, auth()->id());

            $transfer->load([
                'warehouseOrigin',
                'warehouseDestination',
                'sentByUser',
                'items.product'
            ]);

            return ApiResponse::success($transfer, 'Traslado enviado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Traslado no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al enviar el traslado', 500);
        }
    }

    /**
     * Recibir un traslado (cambiar a RECEIVED)
     */
    public function receive($id): JsonResponse
    {
        try {
            $transfer = Transfer::with('items')->findOrFail($id);

            $this->transferService->receiveTransfer($transfer, auth()->id());

            $transfer->load([
                'warehouseOrigin',
                'warehouseDestination',
                'receivedByUser',
                'items.product'
            ]);

            return ApiResponse::success($transfer, 'Traslado recibido exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Traslado no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recibir el traslado', 500);
        }
    }

    /**
     * Cancelar un traslado
     */
    public function cancel($id): JsonResponse
    {
        try {
            $transfer = Transfer::findOrFail($id);

            $this->transferService->cancelTransfer($transfer);

            $transfer->load([
                'warehouseOrigin',
                'warehouseDestination',
                'items.product'
            ]);

            return ApiResponse::success($transfer, 'Traslado cancelado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Traslado no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cancelar el traslado', 500);
        }
    }

    /**
     * Obtener lotes disponibles para un inventario
     */
    public function getAvailableBatches(Request $request): JsonResponse
    {
        try {
            $inventoryId = $request->input('inventory_id');

            if (!$inventoryId) {
                return ApiResponse::error(null, 'Se requiere inventory_id', 400);
            }

            $batches = $this->transferService->getAvailableBatches($inventoryId);

            return ApiResponse::success($batches, 'Lotes disponibles recuperados', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Inventario no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener lotes', 500);
        }
    }

    /**
     * Obtener productos que existen en ambos warehouses
     */
    public function getProductsInBothWarehouses(Request $request): JsonResponse
    {
        try {
            $warehouseOriginId = $request->input('warehouse_origin_id');
            $warehouseDestinationId = $request->input('warehouse_destination_id');

            if (!$warehouseOriginId || !$warehouseDestinationId) {
                return ApiResponse::error(null, 'Se requieren warehouse_origin_id y warehouse_destination_id', 400);
            }

            // Obtener productos que tienen inventario en ambos warehouses
            $productsInOrigin = Inventory::where('warehouse_id', $warehouseOriginId)
                ->where('stock_actual_quantity', '>', 0)
                ->pluck('product_id');

            $productsInDestination = Inventory::where('warehouse_id', $warehouseDestinationId)
                ->pluck('product_id');

            // Productos que están en ambos
            $commonProductIds = $productsInOrigin->intersect($productsInDestination);

            // Obtener detalles de los inventarios
            $inventories = Inventory::with(['product', 'warehouse'])
                ->where('warehouse_id', $warehouseOriginId)
                ->whereIn('product_id', $commonProductIds)
                ->get()
                ->map(function ($inventory) use ($warehouseDestinationId) {
                    // Obtener el inventario destino
                    $inventoryDestination = Inventory::where('warehouse_id', $warehouseDestinationId)
                        ->where('product_id', $inventory->product_id)
                        ->first();

                    return [
                        'product_id' => $inventory->product_id,
                        'product_code' => $inventory->product->code,
                        'product_name' => $inventory->product->name,
                        'inventory_origin_id' => $inventory->id,
                        'inventory_destination_id' => $inventoryDestination->id ?? null,
                        'stock_origin' => $inventory->stock_actual_quantity,
                        'stock_destination' => $inventoryDestination->stock_actual_quantity ?? 0,
                    ];
                });

            return ApiResponse::success($inventories, 'Productos comunes recuperados', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener productos', 500);
        }
    }
}
