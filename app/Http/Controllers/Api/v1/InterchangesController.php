<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\InterchangeStoreRequest;
use App\Http\Requests\Api\v1\InterchangeUpdateRequest;
use App\Http\Resources\Api\v1\InterchangeCollection;
use App\Http\Resources\Api\v1\InterchangeResource;
use App\Models\Api\v1\Interchange;
use App\Models\Equivalent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterchangesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $interchanges = \App\Models\Interchange::with('product')->paginate($perPage);
        return ApiResponse::success($interchanges, 'Intercambios recuperados exitosamente', 200);

    }

    public function store(InterchangeStoreRequest $request): JsonResponse
    {
        try {
            $interchange = \App\Models\Interchange::create($request->validated());
            return ApiResponse::success($interchange, 'Intercambio creado exitosamente', 201);

        }catch (\Exception $exception){
            return ApiResponse::error(null, $exception->getMessage(), 500);
        }


    }
    public function getInterchangeByProduct($id, Request $request): JsonResponse
    {
        try {
            // Obtener intercambios del producto específico con relación al producto
            $interchanges = \App\Models\Interchange::with('product')
                ->where('product_id', $id)
                ->get();

            return ApiResponse::success($interchanges, 'Intercambios recuperados exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No se encontró el intercambio buscado', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
    public function show(Request $request, Interchange $interchange): Response
    {
        return new InterchangeResource($interchange);
    }

    public function update(InterchangeUpdateRequest $request, Interchange $interchange): Response
    {
        $interchange->update($request->validated());

        return new InterchangeResource($interchange);
    }

    public function destroy(Request $request, Interchange $interchange): Response
    {
        $interchange->delete();

        return response()->noContent();
    }
}
