<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SaleItemStoreRequest;
use App\Http\Requests\Api\v1\SaleItemUpdateRequest;
use App\Http\Resources\Api\v1\SaleItemCollection;
use App\Http\Resources\Api\v1\SaleItemResource;
use App\Models\SaleItem;
use App\Models\SalesHeader;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleItemController extends Controller
{
    public function index($id, Request $request): JsonResponse
    {
        log("Sale ID " . $id);
        try {
            $perPage = $request->input('per_page', 100);

            $saleItems = SaleItem::with([
                'inventory:id,product_id',
                'inventory.product:id,original_code,description',
            ])->where('sale_id', $id)->paginate($perPage);
            return ApiResponse::success($saleItems, 'Items de venta recuperados con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Items de venta no encontrados', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(SaleItemStoreRequest $request): JsonResponse
    {
        try {

            $saleItem = SaleItem::create($request->validated());
            //Actualizar el total de la venta
            $sale = SalesHeader::findOrFail($saleItem->sale_id);
            $sale->sale_total += $saleItem->total;
            $sale->save();
            $saleItem['formatted_price'] = '$' . number_format($saleItem->price, 2);

            return ApiResponse::success($saleItem, 'Item de venta creado con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function details($id, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 100);
            $saleItem = SaleItem::with([
                'inventory',
                'inventory.product',
                'inventory.product.category',
                'inventory.warehouse', // Agregar bodega
                'batch', // Agregar lote
            ])
                ->where('sale_id', $id)
                ->where('is_active', true) // Solo items activos
                ->orderBy('created_at', 'desc') // Más recientes primero
                ->paginate($perPage)
                ->through(function ($item) {
                    $item->formatted_price = '$' . number_format($item->price, 2);
                    $item->formatted_total = '$' . number_format($item->total, 2);
                    $item->formatted_discount = '$' . number_format($item->discount, 2);
                    return $item;
                });

            return ApiResponse::success($saleItem, 'Venta recuperada con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
    public function totalSale($id): JsonResponse
    {
        try {
            $total = SaleItem::where('sale_id', $id)->sum('total');
            $neto=number_format($total/1.13,2);
            $iva=number_format(  $neto*0.13,2);
            $saleItem = [
                'total' =>number_format($total,2),
                'neto' => $neto,
                'iva' => $iva,
            ];



            return ApiResponse::success($saleItem, 'Venta recuperada con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            return ApiResponse::success($saleItem, 'Item de venta recuperado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(SaleItemUpdateRequest $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            $validated = $request->validated();

            // Obtener valores actuales o nuevos
            $quantity = $validated['quantity'] ?? $saleItem->quantity;
            $price = $validated['price'] ?? $saleItem->price;
            // discount almacena el PORCENTAJE (0-25)
            $discountPercentage = $validated['discount'] ?? $saleItem->discount ?? 0;

            // Validar que el descuento esté en el rango permitido (0-25)
            if ($discountPercentage < 0 || $discountPercentage > 25) {
                return ApiResponse::error(
                    null,
                    'El descuento debe estar entre 0% y 25%',
                    422
                );
            }

            // Validar que el descuento sea un número entero (sin decimales)
            if (floor($discountPercentage) != $discountPercentage) {
                return ApiResponse::error(
                    null,
                    'El descuento no puede tener decimales',
                    422
                );
            }

            // Validar que la cantidad no sea decimal
            if (floor($quantity) != $quantity) {
                return ApiResponse::error(
                    null,
                    'La cantidad no puede tener decimales',
                    422
                );
            }

            // VALIDAR STOCK DISPONIBLE
            $inventory = \App\Models\Inventory::find($saleItem->inventory_id);
            if (!$inventory) {
                return ApiResponse::error(
                    null,
                    'Inventario no encontrado',
                    404
                );
            }

            // Calcular stock total disponible en lotes
            $availableStock = \App\Models\InventoriesBatch::where('id_inventory', $inventory->id)
                ->sum('quantity');

            // Si estamos aumentando la cantidad, validar que haya stock
            if ($quantity > $saleItem->quantity) {
                $additionalQuantity = $quantity - $saleItem->quantity;

                if ($availableStock < $additionalQuantity) {
                    return ApiResponse::error(
                        ['available_stock' => $availableStock],
                        "Stock insuficiente. Solo hay {$availableStock} unidades disponibles",
                        422
                    );
                }
            }

            // Calcular subtotal
            $subtotal = $quantity * $price;

            // Calcular descuento en dinero basado en el porcentaje
            $discountMoney = $subtotal * ($discountPercentage / 100);

            // Calcular total final
            $total = $subtotal - $discountMoney;

            // Actualizar todos los campos calculados
            $validated['quantity'] = $quantity;
            $validated['price'] = $price;
            $validated['discount'] = $discountPercentage; // Guardar el porcentaje en discount
            $validated['total'] = $total;

            $saleItem->update($validated);

            // Recalcular total de la venta
            $sale = SalesHeader::findOrFail($saleItem->sale_id);
            $sale->sale_total = SaleItem::where('sale_id', $sale->id)->sum('total');
            $sale->save();

            return ApiResponse::success($saleItem, 'Item de venta actualizado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $saleItem = SaleItem::findOrFail($id);
            $saleItem->delete();
            return ApiResponse::success(null, 'Item de venta eliminado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Item de venta no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }
}
