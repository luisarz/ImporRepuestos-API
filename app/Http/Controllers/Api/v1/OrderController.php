<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderController - Controlador para gestionar órdenes
 * Usa el modelo Order que apunta a sales_header con is_order=true
 */
class OrderController extends Controller
{
    /**
     * Listar órdenes con paginación, búsqueda y filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $filtersJson = $request->input('filters') ?? '[]';
            $filters = json_decode($filtersJson, true) ?? [];

            $query = Order::with([
                'customer:id,document_number,name,last_name,document_type_id',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
                'documentType',
                'paymentMethod',
                'operationCondition',
            ]);

            // Búsqueda general
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                        ->orWhere('document_internal_number', 'like', "%$search%")
                        ->orWhere('sale_total', 'like', "%$search%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%")
                                ->orWhere('document_number', 'like', "%$search%");
                        })
                        ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                            $sellerQuery->where('name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%");
                        });
                });
            }

            // Aplicar filtros
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if (isset($filter['field']) && isset($filter['value'])) {
                        $field = $filter['field'];
                        $value = $filter['value'];

                        if ($field === 'sale_status' && !empty($value)) {
                            $query->where('sale_status', $value);
                        } elseif ($field === 'customer_id' && !empty($value)) {
                            $query->where('customer_id', $value);
                        } elseif ($field === 'warehouse_id' && !empty($value)) {
                            $query->where('warehouse_id', $value);
                        } elseif ($field === 'seller_id' && !empty($value)) {
                            $query->where('seller_id', $value);
                        } elseif ($field === 'payment_status' && !empty($value)) {
                            $query->where('payment_status', $value);
                        } elseif ($field === 'date_from' && !empty($value)) {
                            $query->whereDate('sale_date', '>=', $value);
                        } elseif ($field === 'date_to' && !empty($value)) {
                            $query->whereDate('sale_date', '<=', $value);
                        } elseif ($field === 'exclude_status' && !empty($value)) {
                            $query->where('sale_status', '!=', $value);
                        }
                    }
                }
            }

            // Ordenar por fecha más reciente primero
            $query->orderBy('sale_date', 'desc')->orderBy('id', 'desc');

            $orders = $query->paginate($perPage);

            return ApiResponse::success($orders, 'Órdenes recuperadas con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recuperar órdenes', 500);
        }
    }

    /**
     * Obtener una orden por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $order = Order::with([
                'customer',
                'warehouse',
                'seller',
                'documentType',
                'paymentMethod',
                'operationCondition',
                'items',
            ])->findOrFail($id);

            return ApiResponse::success($order, 'Orden recuperada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al recuperar orden', 500);
        }
    }

    /**
     * Crear nueva orden
     */
    public function store(SalesHeaderStoreRequest $request): JsonResponse
    {
        try {
            $order = Order::create($request->validated());
            return ApiResponse::success($order, 'Orden creada con éxito', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al crear orden', 500);
        }
    }

    /**
     * Actualizar orden existente
     */
    public function update(SalesHeaderUpdateRequest $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $order->update($request->validated());

            return ApiResponse::success($order, 'Orden actualizada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar orden', 500);
        }
    }

    /**
     * Eliminar orden (soft delete si está habilitado)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();

            return ApiResponse::success(null, 'Orden eliminada con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar orden', 500);
        }
    }

    /**
     * Obtener total calculado de una orden
     */
    public function getTotal($id): JsonResponse
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            $total = $order->items()->where('is_active', true)->sum('total');
            $neto = $total / 1.13;
            $iva = $neto * 0.13;

            $result = [
                'total' => number_format($total, 2),
                'neto' => number_format($neto, 2),
                'iva' => number_format($iva, 2),
            ];

            return ApiResponse::success($result, 'Total calculado con éxito', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al calcular total', 500);
        }
    }
}
