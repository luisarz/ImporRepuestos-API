<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CustomerAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\CustomerAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\CustomerAddressCatalogCollection;
use App\Http\Resources\Api\v1\CustomerAddressCatalogResource;
use App\Models\CustomerAddressCatalog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerAddressCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $isDefault = $request->input('is_default', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = CustomerAddressCatalog::query()->with(['customer:id,name,email', 'district:id,description']);

            // Búsqueda por múltiples campos
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('address', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por dirección por defecto
            if ($isDefault !== '') {
                $query->where('is_default', $isDefault);
            }

            // Aplicar ordenamiento
            $allowedSortFields = ['id', 'customer_id', 'address', 'city', 'is_default', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $customerAddressCatalogs = $query->paginate($perPage);
            return ApiResponse::success($customerAddressCatalogs, 'Direcciones de cliente listadas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(CustomerAddressCatalogStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Verificar si es la primera dirección del cliente
            $existingAddresses = CustomerAddressCatalog::where('customer_id', $data['customer_id'])->count();

            // Si es la primera dirección, marcarla como predeterminada automáticamente
            if ($existingAddresses === 0) {
                $data['is_default'] = 1;
            }

            // Si se marca como predeterminada, desmarcar todas las demás del mismo cliente
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                CustomerAddressCatalog::where('customer_id', $data['customer_id'])
                    ->update(['is_default' => 0]);
            }

            $customerAddressCatalog = CustomerAddressCatalog::create($data);
            $customerAddressCatalog->load(['customer:id,name,email', 'district:id,description']);

            return ApiResponse::success($customerAddressCatalog, 'Registro creado correctamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            return ApiResponse::success($customerAddressCatalog, 'Registro obtenido correctamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(CustomerAddressCatalogUpdateRequest $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            $data = $request->validated();

            // Si se marca como predeterminada, desmarcar todas las demás del mismo cliente
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                CustomerAddressCatalog::where('customer_id', $customerAddressCatalog->customer_id)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => 0]);
            }

            $customerAddressCatalog->update($data);
            $customerAddressCatalog->load(['customer:id,name,email', 'district:id,description']);

            return ApiResponse::success($customerAddressCatalog, 'Registro actualizado correctamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $customerAddressCatalog = CustomerAddressCatalog::findOrFail($id);
            $wasDefault = $customerAddressCatalog->is_default;
            $customerId = $customerAddressCatalog->customer_id;

            $customerAddressCatalog->delete();

            // Si era la dirección predeterminada, marcar otra como predeterminada
            if ($wasDefault) {
                $nextAddress = CustomerAddressCatalog::where('customer_id', $customerId)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($nextAddress) {
                    $nextAddress->is_default = 1;
                    $nextAddress->save();
                }
            }

            return ApiResponse::success(null, 'Registro eliminado correctamente', 200);
        } catch (ModelNotFoundException $exception) {
            return ApiResponse::error(null, 'Registro no encontrado', 404);
        } catch (\Exception $exception) {
            return ApiResponse::error($exception->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = CustomerAddressCatalog::count();
            $defaults = CustomerAddressCatalog::where('is_default', 1)->count();
            $nonDefaults = CustomerAddressCatalog::where('is_default', 0)->count();

            return ApiResponse::success([
                'total' => $total,
                'active' => $defaults,
                'inactive' => $nonDefaults
            ], 'Estadísticas obtenidas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        try {
            $address = CustomerAddressCatalog::findOrFail($id);

            // Desmarcar todas las direcciones del mismo cliente como predeterminadas
            CustomerAddressCatalog::where('customer_id', $address->customer_id)
                ->update(['is_default' => 0]);

            // Marcar la dirección actual como predeterminada
            $address->is_default = 1;
            $address->save();

            return ApiResponse::success($address, 'Dirección establecida como predeterminada', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Dirección no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function deleteBatch(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            // Obtener las direcciones que se van a eliminar
            $addressesToDelete = CustomerAddressCatalog::whereIn('id', $ids)->get();
            $affectedCustomers = [];

            // Recopilar clientes que tenían dirección predeterminada eliminada
            foreach ($addressesToDelete as $address) {
                if ($address->is_default) {
                    $affectedCustomers[] = $address->customer_id;
                }
            }

            // Eliminar las direcciones
            CustomerAddressCatalog::whereIn('id', $ids)->delete();

            // Para cada cliente afectado, marcar otra dirección como predeterminada
            foreach (array_unique($affectedCustomers) as $customerId) {
                $nextAddress = CustomerAddressCatalog::where('customer_id', $customerId)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($nextAddress) {
                    $nextAddress->is_default = 1;
                    $nextAddress->save();
                }
            }

            return ApiResponse::success(null, 'Direcciones eliminadas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
