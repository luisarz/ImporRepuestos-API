<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\SalesHeaderStoreRequest;
use App\Http\Requests\Api\v1\SalesHeaderUpdateRequest;
use App\Models\Order;
use App\Models\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

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

            // Obtener warehouse_id de la sesión del usuario
            $warehouseId = $request->user()->warehouse_id ?? null;

            $query = Order::with([
                'customer:id,document_number,name,last_name,document_type_id',
                'warehouse:id,name',
                'seller:id,name,last_name,dui',
                'documentType',
                'paymentMethod',
                'operationCondition',
            ]);

            // Filtrar por sucursal del usuario por defecto
            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            // Búsqueda general
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                        ->orWhere('order_number', 'like', "%$search%")
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
            $data = $request->validated();

            // Generar el siguiente número de orden para esta sucursal
            if (!isset($data['order_number']) && isset($data['warehouse_id'])) {
                $lastOrder = Order::where('warehouse_id', $data['warehouse_id'])
                    ->orderBy('order_number', 'desc')
                    ->first();

                $data['order_number'] = $lastOrder ? $lastOrder->order_number + 1 : 1;
            }

            $order = Order::create($data);
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
     * Obtener estadísticas de órdenes
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            // Obtener warehouse_id de la sesión del usuario
            $warehouseId = $request->user()->warehouse_id ?? null;

            $query = Order::query();

            // Filtrar por sucursal si existe
            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $total = (clone $query)->count();
            $inProgress = (clone $query)->where('sale_status', 1)->count(); // En progreso
            $completed = (clone $query)->where('sale_status', 2)->count(); // Completadas
            $cancelled = (clone $query)->where('sale_status', 3)->count(); // Anuladas

            $result = [
                'total' => $total,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'cancelled' => $cancelled,
            ];

            return ApiResponse::success($result, 'Estadísticas recuperadas con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
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

    /**
     * Generar PDF de la orden
     */
    public function printPdf($id): JsonResponse
    {
        try {
            // Obtener la orden con sus relaciones
            $order = Order::with([
                'customer',
                'warehouse',
                'seller',
                'operationCondition',
                'items.inventory.product', // Cargar producto a través de inventory
            ])->findOrFail($id);

            // Obtener información de la empresa
            $empresa = Company::first();

            // Preparar logo como base64 - prioridad: logo de sucursal, sino logo de empresa
            $logo = null;
            if ($order && $order->warehouse && $order->warehouse->logo) {
                $logo = $order->warehouse->logo;
            } elseif ($empresa && $empresa->logo_path) {
                $logo = $empresa->logo_path;
            }

            // Si el logo es un array, extraer el path
            if ($logo && is_array($logo)) {
                $logo = $logo['path'] ?? $logo['filename'] ?? $logo[0] ?? null;
            }

            // Si el logo es un string JSON, extraer el path
            if ($logo && is_string($logo) && (str_starts_with($logo, '{') || str_starts_with($logo, '['))) {
                $logoArray = json_decode($logo, true);
                if (is_array($logoArray)) {
                    $logo = $logoArray['path'] ?? $logoArray['filename'] ?? $logoArray[0] ?? null;
                }
            }

            // Convertir logo a base64 data URL
            $logoDataUrl = null;
            if ($logo && is_string($logo)) {
                $logoPath = public_path('storage/' . $logo);
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = base64_encode($logoData);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $logoPath);
                    finfo_close($finfo);
                    $logoDataUrl = 'data:' . $mimeType . ';base64,' . $logoBase64;
                }
            }

            // Generar PDF
            $pdf = Pdf::loadView('orders.order-print-pdf', [
                'order' => $order,
                'empresa' => $empresa,
                'logo' => $logoDataUrl,
            ]);

            // Configurar tamaño de página
            $pdf->setPaper('letter', 'portrait');

            // Retornar PDF como base64
            $pdfContent = $pdf->output();
            $pdfBase64 = base64_encode($pdfContent);

            return ApiResponse::success([
                'pdf' => $pdfBase64,
                'filename' => 'orden_' . str_pad($order->order_number, 5, '0', STR_PAD_LEFT) . '.pdf',
            ], 'PDF generado con éxito', 200);

        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enviar orden por correo electrónico
     */
    public function sendByEmail(Request $request, $id): JsonResponse
    {
        try {
            // Validar email
            $request->validate([
                'email' => 'required|email',
                'message' => 'nullable|string',
            ]);

            // Obtener la orden con sus relaciones
            $order = Order::with([
                'customer',
                'warehouse',
                'seller',
                'operationCondition',
                'items.inventory.product', // Cargar producto a través de inventory
            ])->findOrFail($id);

            // Obtener información de la empresa
            $empresa = Company::first();

            // Preparar logo como base64 - prioridad: logo de sucursal, sino logo de empresa
            $logo = null;
            if ($order && $order->warehouse && $order->warehouse->logo) {
                $logo = $order->warehouse->logo;
            } elseif ($empresa && $empresa->logo_path) {
                $logo = $empresa->logo_path;
            }

            // Si el logo es un array, extraer el path
            if ($logo && is_array($logo)) {
                $logo = $logo['path'] ?? $logo['filename'] ?? $logo[0] ?? null;
            }

            // Si el logo es un string JSON, extraer el path
            if ($logo && is_string($logo) && (str_starts_with($logo, '{') || str_starts_with($logo, '['))) {
                $logoArray = json_decode($logo, true);
                if (is_array($logoArray)) {
                    $logo = $logoArray['path'] ?? $logoArray['filename'] ?? $logoArray[0] ?? null;
                }
            }

            // Convertir logo a base64 data URL
            $logoDataUrl = null;
            if ($logo && is_string($logo)) {
                $logoPath = public_path('storage/' . $logo);
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = base64_encode($logoData);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $logoPath);
                    finfo_close($finfo);
                    $logoDataUrl = 'data:' . $mimeType . ';base64,' . $logoBase64;
                }
            }

            // Generar PDF
            $pdf = Pdf::loadView('orders.order-print-pdf', [
                'order' => $order,
                'empresa' => $empresa,
                'logo' => $logoDataUrl,
            ]);

            $pdf->setPaper('letter', 'portrait');
            $pdfContent = $pdf->output();

            // Preparar datos para el email
            $customerName = $order->customer
                ? trim($order->customer->name . ' ' . ($order->customer->last_name ?? ''))
                : 'Cliente';

            $orderStatus = match($order->sale_status) {
                1 => 'En Progreso',
                2 => 'Completada',
                3 => 'Anulada',
                default => 'Desconocido'
            };

            $emailData = [
                'customerName' => $customerName,
                'orderNumber' => str_pad($order->order_number, 5, '0', STR_PAD_LEFT),
                'orderDate' => \Carbon\Carbon::parse($order->sale_date)->format('d/m/Y H:i'),
                'orderTotal' => number_format($order->sale_total, 2),
                'orderStatus' => $orderStatus,
                'companyName' => $empresa->name ?? 'IMPORREPUESTOS',
                'companyPhone' => $empresa->phone ?? null,
                'companyEmail' => $empresa->email ?? null,
                'companyAddress' => $empresa->address ?? null,
                'additionalMessage' => $request->input('message', null),
            ];

            // Enviar email
            Mail::send('emails.sendOrder', $emailData, function ($message) use ($request, $pdfContent, $order, $empresa) {
                $message->to($request->input('email'))
                    ->subject('Orden de Compra #' . str_pad($order->order_number, 5, '0', STR_PAD_LEFT))
                    ->from(
                        $empresa->email ?? config('mail.from.address', 'noreply@imporrepuestos.com'),
                        $empresa->name ?? config('mail.from.name', 'IMPORREPUESTOS')
                    )
                    ->attachData($pdfContent, 'orden_' . str_pad($order->order_number, 5, '0', STR_PAD_LEFT) . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            return ApiResponse::success([
                'email' => $request->input('email'),
                'order_number' => str_pad($order->order_number, 5, '0', STR_PAD_LEFT),
            ], 'Orden enviada por correo con éxito', 200);

        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error($exception->getMessage(), 'Orden no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al enviar correo: ' . $e->getMessage(), 500);
        }
    }
}
