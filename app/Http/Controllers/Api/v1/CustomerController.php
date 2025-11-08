<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerStoreRequest;
use App\Http\Requests\Api\v1\CustomerUpdateRequest;
use App\Http\Resources\Api\v1\CustomerCollection;
use App\Http\Resources\Api\v1\CustomerResource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            // El DataTable envía 'sortField' y 'sortOrder'
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            // Convertir a minúsculas y validar
            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc'; // Valor por defecto si no es válido
            }

            $query = Customer::query()->with('documentType','warehouse','departamento','municipio');

            // Búsqueda por múltiples campos
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%")
                      ->orWhere('nit', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Aplicar ordenamiento - solo campos propios del modelo
            $allowedSortFields = ['id', 'name', 'last_name', 'document_number', 'nit', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $customers = $query->paginate($perPage);
            return ApiResponse::success($customers, 'Clientes recuperados con éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(CustomerStoreRequest $request): JsonResponse
    {
        try {
            $customer = (new Customer)->create($request->validated());
            return ApiResponse::success($customer, 'Cliente almacenado de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customer = Customer::with('address')->findOrFail($id);
            return ApiResponse::success($customer, 'Cliente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No existe el cliente', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerUpdateRequest $request, $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($request->validated());

            return ApiResponse::success($customer, 'Cliente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No existe el cliente', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, Customer $customer): Response
    {
        $customer->delete();

        return response()->noContent();
    }

    /**
     * Obtener estadísticas de clientes
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total' => Customer::count(),
                'active' => Customer::where('is_active', 1)->count(),
                'inactive' => Customer::where('is_active', 0)->count(),
                'withCredit' => Customer::where('is_creditable', 1)->count(),
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener múltiples clientes por IDs
     */
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $customers = Customer::whereIn('id', $ids)
                ->with('documentType','warehouse','departamento','municipio')
                ->get();

            return ApiResponse::success($customers, 'Clientes recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Activar múltiples clientes
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Customer::whereIn('id', $ids)->update(['is_active' => 1]);

            return ApiResponse::success(null, 'Clientes activados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Desactivar múltiples clientes
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Customer::whereIn('id', $ids)->update(['is_active' => 0]);

            return ApiResponse::success(null, 'Clientes desactivados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Eliminar múltiples clientes
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Customer::whereIn('id', $ids)->delete();

            return ApiResponse::success(null, 'Clientes eliminados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}