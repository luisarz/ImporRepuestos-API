<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SaleItemStoreRequest;
use App\Http\Requests\Api\v1\SaleItemUpdateRequest;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderItemController - Controlador para gestionar items de órdenes
 * Usa el modelo OrderItem que apunta a sale_items
 */
class OrderItemController extends Controller
{
    /**
     * Listar items de una orden
     */
    public function index($orderId, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 100);

            $orderItems = OrderItem::with([
                'inventory:id,product_id',
                'inventory.product:id,original_code,description',
            ])->where('sale_id', $orderId)->paginate($perPage);

            return ApiResponse::success($orderItems, 'Items recuperados con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recuperar items', 500);
        }
    }

    /**
     * Obtener details de items de una orden (con relaciones completas)
     */
    public function details($orderId, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 100);

            $orderItems = OrderItem::with([
                'inventory',
                'inventory.product',
                'inventory.product.category',
                'inventory.warehouse',
                'batch',
            ])
                ->where('sale_id', $orderId)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->through(function ($item) {
                    $item->formatted_price = '$' . number_format($item->price, 2);
                    $item->formatted_total = '$' . number_format($item->total, 2);
                    $item->formatted_discount = '$' . number_format($item->discount, 2);
                    return $item;
                });

            return ApiResponse::success($orderItems, 'Items recuperados con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recuperar items', 500);
        }
    }

    /**
     * Obtener un item específico
     */
    public function show($id): JsonResponse
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            return ApiResponse::success($orderItem, 'Item recuperado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recuperar item', 500);
        }
    }

    /**
     * Crear nuevo item de orden
     */
    public function store(SaleItemStoreRequest $request): JsonResponse
    {
        try {
            $orderItem = OrderItem::create($request->validated());

            // Recalcular totales de la orden
            $this->recalculateOrderTotals($orderItem->sale_id);

            $orderItem['formatted_price'] = '$' . number_format($orderItem->price, 2);

            return ApiResponse::success($orderItem, 'Item agregado con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al agregar item', 500);
        }
    }

    /**
     * Actualizar item de orden
     */
    public function update(SaleItemUpdateRequest $request, $id): JsonResponse
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            $validated = $request->validated();

            // Obtener valores actuales o nuevos
            $quantity = $validated['quantity'] ?? $orderItem->quantity;
            $price = $validated['price'] ?? $orderItem->price;
            $discountPercentage = $validated['discount'] ?? $orderItem->discount ?? 0;

            // Validaciones
            if ($discountPercentage < 0 || $discountPercentage > 25) {
                return ApiResponse::error(null, 'El descuento debe estar entre 0% y 25%', 422);
            }

            if (floor($discountPercentage) != $discountPercentage) {
                return ApiResponse::error(null, 'El descuento no puede tener decimales', 422);
            }

            if (floor($quantity) != $quantity) {
                return ApiResponse::error(null, 'La cantidad no puede tener decimales', 422);
            }

            // Calcular subtotal y total
            $subtotal = $quantity * $price;
            $discountMoney = $subtotal * ($discountPercentage / 100);
            $total = $subtotal - $discountMoney;

            // Actualizar campos
            $validated['quantity'] = $quantity;
            $validated['price'] = $price;
            $validated['discount'] = $discountPercentage;
            $validated['total'] = $total;

            $orderItem->update($validated);

            // Recalcular totales de la orden
            $this->recalculateOrderTotals($orderItem->sale_id);

            return ApiResponse::success($orderItem, 'Item actualizado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar item', 500);
        }
    }

    /**
     * Eliminar item de orden
     */
    public function destroy($id): JsonResponse
    {
        try {
            $orderItem = OrderItem::findOrFail($id);
            $orderId = $orderItem->sale_id;
            $orderItem->delete();

            // Recalcular totales de la orden
            $this->recalculateOrderTotals($orderId);

            return ApiResponse::success(null, 'Item eliminado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar item', 500);
        }
    }

    /**
     * Calcular total de la orden
     */
    public function totalOrder($orderId): JsonResponse
    {
        try {
            $total = OrderItem::where('sale_id', $orderId)
                ->where('is_active', true)
                ->sum('total');

            $neto = number_format($total / 1.13, 2);
            $iva = number_format($neto * 0.13, 2);

            $result = [
                'total' => number_format($total, 2),
                'neto' => $neto,
                'iva' => $iva,
            ];

            return ApiResponse::success($result, 'Total calculado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al calcular total', 500);
        }
    }

    /**
     * Recalcular totales de la orden
     */
    private function recalculateOrderTotals($orderId): void
    {
        $order = Order::findOrFail($orderId);

        $total = OrderItem::where('sale_id', $orderId)
            ->where('is_active', true)
            ->sum('total');

        $neto = $total / 1.13;
        $iva = $neto * 0.13;

        $order->sale_total = round($total, 2);
        $order->net_amount = round($neto, 2);
        $order->tax = round($iva, 2);
        $order->save();
    }
}
