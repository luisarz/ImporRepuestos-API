<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PriceStoreRequest;
use App\Http\Requests\Api\v1\PriceUpdateRequest;
use App\Http\Resources\Api\v1\PriceCollection;
use App\Http\Resources\Api\v1\PriceResource;
use App\Models\Price;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PriceController extends Controller
{


    public function store(PriceStoreRequest $request): JsonResponse
    {
        try {

            $price = (new \App\Models\Price)->create($request->validated());
           return ApiResponse::success($price, 'Precio creado', 201);
        } catch (\Exception $e) {
         return ApiResponse::error($e, 'Error al crear precio', 500);
        }
    }

    public function show(Request $request, $idInventario): JsonResponse
    {
        try {
            $prices = (new Price)->where('inventory_id', $idInventario)->get();
            if ($prices->isEmpty()) {
                return ApiResponse::error('No se encontraron precios para el inventario', 404);
            }
            return ApiResponse::success($prices, 'Precios recuperados', 200);
        }
        catch (\Exception $e) {
            return ApiResponse::error(null, 'Error al recuperar precios', 500);
        }
    }

    public function update(PriceUpdateRequest $request, $id): JsonResponse
    {
        try {
            $price = (new \App\Models\Price)->findOrFail($id);
            $price->update($request->validated());
            return ApiResponse::success($price, 'Precio actualizado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Precio no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al actualizar precio', 500);
        }
    }
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $price= (new \App\Models\Price)->findOrFail($id);
            $price->delete();
            return ApiResponse::success(null, 'Precio eliminado', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Precio no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar precio', 500);
        }
    }
}
