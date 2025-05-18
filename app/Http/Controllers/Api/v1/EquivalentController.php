<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EquivalentStoreRequest;
use App\Http\Requests\Api\v1\EquivalentUpdateRequest;
use App\Http\Resources\Api\v1\EquivalentCollection;
use App\Http\Resources\Api\v1\EquivalentResource;
use App\Models\Application;
use App\Models\Equivalent;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EquivalentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 5);
            $equivalents = Equivalent::with([
                'productOriginal:id,code,barcode,description',
                'productEquivalent:id,code,barcode,description,brand_id',
                'productEquivalent.brand',
            ])->select('id', 'product_id', 'product_id_equivalent')->paginate(3);
            return ApiResponse::success($equivalents, 'Equivalentes recuperada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No se encontró el equivalente buscada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function getEquivalentByProduct($id,Request $request): JsonResponse
    {
        \Illuminate\Log\log($request->all());
        \Illuminate\Log\log($id);
        try {
            $id_product = $request->input('$id');

            $equivalents = Equivalent::with([
                'productOriginal:id,code,barcode,description,brand_id',
                'productEquivalent:id,code,barcode,description',
                'productEquivalent.brand',
            ])->where('product_id', $id)->paginate(10);



            return ApiResponse::success($equivalents, 'Equivalentes recuperada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No se encontró el equivalente buscada', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(EquivalentStoreRequest $request): JsonResponse
    {
        try {
            //validar si el producto original y el producto equivalente son iguales
            if ($request->product_id == $request->product_id_equivalent) {
                return ApiResponse::error(null, 'El producto original y el producto equivalente no pueden ser iguales', 200);
            }
            //verificar si ya existe el equivalente
            $equivalent = Equivalent::where('product_id', $request->product_id)
                ->where('product_id_equivalent', $request->product_id_equivalent)
                ->first();
            if ($equivalent) {
                return ApiResponse::error(null, 'El producto equivalente ya existe', 200);
            }

            $equivalent = (new Equivalent)->create($request->validated());

            return ApiResponse::success($equivalent, 'Equivalente recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Equivalente no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrio un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $equivalent = (new Equivalent)->findOrFail($id);
            return ApiResponse::success($equivalent, 'Producto equivalente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Equivalente no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(EquivalentUpdateRequest $request, $id): JsonResponse
    {
        try {
            $equivalent = (new \App\Models\Equivalent)->findOrFail($id);
            $equivalent->update($request->validated());
            return ApiResponse::success($equivalent, 'Producto equivalente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Equivalente no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {


        try {
            $equivalent = (new \App\Models\Equivalent)->findOrFail($id);
            $equivalent->delete();
            return ApiResponse::success($equivalent, 'Producto equivalente recuperado con éxito', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Equivalente no encontrado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
