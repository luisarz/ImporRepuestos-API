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
    public function index(Request $request): Response
    {
        $interchanges = \App\Models\Interchange::all();

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
    public function getInterchangeByProduct($id,Request $request): JsonResponse
    {
        \Illuminate\Log\log($request->all());
        \Illuminate\Log\log($id);
        try {
            $id_product = $request->input('$id');

            $equivalents = \App\Models\Interchange::where('product_id', $id)->paginate(10);



            return ApiResponse::success($equivalents, 'Equivalentes recuperada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'No se encontró el equivalente buscada', 404);
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
