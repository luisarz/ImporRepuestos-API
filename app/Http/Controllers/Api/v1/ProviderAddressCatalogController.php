<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ProviderAddressCatalogStoreRequest;
use App\Http\Requests\Api\v1\ProviderAddressCatalogUpdateRequest;
use App\Http\Resources\Api\v1\ProviderAddressCatalogCollection;
use App\Http\Resources\Api\v1\ProviderAddressCatalogResource;
use App\Models\ProviderAddressCatalog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderAddressCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $statusFilter = $request->input('is_active', '');
            $providerIdFilter = $request->input('provider_id', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = ProviderAddressCatalog::query()->with(['provider:id,legal_name,comercial_name', 'district:id,description']);

            // Filtro por proveedor específico
            if (!empty($providerIdFilter)) {
                $query->where('provider_id', $providerIdFilter);
            }

            // Búsqueda por múltiples campos
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('address_reference', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('seller', 'like', "%{$search}%")
                      ->orWhereHas('provider', function($q) use ($search) {
                          $q->where('legal_name', 'like', "%{$search}%")
                            ->orWhere('comercial_name', 'like', "%{$search}%");
                      });
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Aplicar ordenamiento
            $allowedSortFields = ['id', 'provider_id', 'address_reference', 'email', 'phone', 'seller', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $providerAddressCatalogs = $query->paginate($perPage);
            return ApiResponse::success($providerAddressCatalogs, 'Direcciones de proveedor listados correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(ProviderAddressCatalogStoreRequest $request): JsonResponse
    {
        try {
            $providerAddressCatalog = (new \App\Models\ProviderAddressCatalog)->create($request->validated());
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor creada correctamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor obtenida correctamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
        } catch (\Exception $e) {
            // Capturamos cualquier otro tipo de error
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }


    public function update(ProviderAddressCatalogUpdateRequest $request,$id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);

            $providerAddressCatalog->update($request->validated());
            return ApiResponse::success(new ProviderAddressCatalogResource($providerAddressCatalog), 'Dirección de proveedor actualizada correctamente', 200);

        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $providerAddressCatalog = (new ProviderAddressCatalog)->findOrFail($id);
            if(!$providerAddressCatalog) {
                return ApiResponse::error('Dirección de proveedor no encontrada', 'Dirección de proveedor no encontrada', 404);
            }
            $providerAddressCatalog->delete();
           return ApiResponse::success(null, 'Dirección de proveedor eliminada correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = ProviderAddressCatalog::count();
            $active = ProviderAddressCatalog::where('is_active', 1)->count();
            $inactive = ProviderAddressCatalog::where('is_active', 0)->count();

            return ApiResponse::success([
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ], 'Estadísticas obtenidas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProviderAddressCatalog::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Direcciones activadas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProviderAddressCatalog::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Direcciones desactivadas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            ProviderAddressCatalog::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Direcciones eliminadas correctamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
