<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\WarehouseStoreRequest;
use App\Http\Requests\Api\v1\WarehouseUpdateRequest;
use App\Http\Resources\Api\v1\WarehouseCollection;
use App\Http\Resources\Api\v1\WarehouseResource;
use App\Models\Warehouse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WarehouseController extends Controller
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

            $query = Warehouse::query()->with('stablishmentType', 'district', 'economicActivity');

            // Búsqueda por múltiples campos
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nrc', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Aplicar ordenamiento - solo campos propios del modelo
            $allowedSortFields = ['id', 'name', 'nrc', 'phone', 'email', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $warehouses = $query->paginate($perPage);
            return ApiResponse::success($warehouses, 'Lista de sucursales', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(WarehouseStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Manejar la carga de logo
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('warehouses/logos', $filename, 'public');

                $data['logo'] = [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename
                ];
            }

            $warehouse = (new Warehouse)->create($data);

            return ApiResponse::success(new WarehouseResource($warehouse), 'Sucursal aperturada de manera exitosa!', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Sucursal no aperturada', 400);
        }
    }


    public function show($id): JsonResponse
    {
        try {
            $warehouse = Warehouse::find($id);
            if (!$warehouse) {
                return ApiResponse::error(null, 'Sucursal no encontrada', 404);
            }
            return ApiResponse::success($warehouse, 'Detalle de la sucursal', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Sucursal no encontrada', 404);
        }
    }

    public function update(WarehouseUpdateRequest $request, $id): JsonResponse
    {
        try {
            $warehouse = Warehouse::find($id);
            if (!$warehouse) {
                return ApiResponse::error(null, 'Sucursal no encontrada', 404);
            }

            $data = $request->validated();

            // Manejar la carga de logo
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($warehouse->logo && isset($warehouse->logo['path'])) {
                    \Storage::disk('public')->delete($warehouse->logo['path']);
                }

                $file = $request->file('logo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('warehouses/logos', $filename, 'public');

                $data['logo'] = [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename
                ];
            }

            $warehouse->update($data);
            return ApiResponse::success(new WarehouseResource($warehouse), 'Sucursal actualizada de manera exitosa', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Sucursal no actualizada', 400);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            $warehouse = (new Warehouse)->findOrFail($id);
            $warehouse->delete();
            return ApiResponse::success(null, 'Sucursal eliminada de manera exitosa', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Sucursal no encontrada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Sucursal no eliminada', 400);
        }
    }

    /**
     * Obtener todas las sucursales activas (sin paginar)
     * Para uso en selects y formularios
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $warehouses = Warehouse::where('is_active', 1)
                ->select('id', 'name', 'phone', 'email')
                ->orderBy('name')
                ->get();
            return ApiResponse::success($warehouses, 'Sucursales recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    // Estadísticas
    public function stats(): JsonResponse
    {
        try {
            $total = Warehouse::count();
            $active = Warehouse::where('is_active', 1)->count();
            $inactive = Warehouse::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ];

            return ApiResponse::success($stats, 'Estadísticas de almacenes obtenidas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener estadísticas', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $warehouses = Warehouse::whereIn('id', $ids)
                ->with('stablishmentType', 'district', 'economicActivity', 'company')
                ->get();
            return ApiResponse::success($warehouses, 'Almacenes recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Warehouse::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Almacenes activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Warehouse::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Almacenes desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Warehouse::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Almacenes eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

}
