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
            $warehouses = Warehouse::with('stablishmentType', 'district', 'economicActivity')->paginate(10);
            return ApiResponse::success($warehouses, 'Lista de sucursales', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(WarehouseStoreRequest $request): JsonResponse
    {
        try {
            $warehouse = (new Warehouse)->create($request->validated());

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
            $warehouse->update($request->validated());
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
