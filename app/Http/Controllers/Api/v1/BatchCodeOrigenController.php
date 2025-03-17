<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\BatchCodeOrigenStoreRequest;
use App\Http\Requests\Api\v1\BatchCodeOrigenUpdateRequest;
use App\Http\Resources\Api\v1\BatchCodeOrigenCollection;
use App\Http\Resources\Api\v1\BatchCodeOrigenResource;
use App\Models\BatchCodeOrigen;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BatchCodeOrigenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            $batchCodeOrigens = BatchCodeOrigen::paginate($perPage);
            return ApiResponse::success($batchCodeOrigens, 'Código recuperados', 200);

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(BatchCodeOrigenStoreRequest $request): JsonResponse
    {
        try {
            $batchCodeOrigen = (new BatchCodeOrigen)->create($request->validated());
            return ApiResponse::success($batchCodeOrigen, 'Código generado cone éxito', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $batchCodeOrigen = (new \App\Models\BatchCodeOrigen)->findOrFail($id);
            return ApiResponse::success($batchCodeOrigen, 'Código recuperado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Código no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e, 'Ocurrió un erro', 500);
        }
    }

    public function update(BatchCodeOrigenUpdateRequest $request, $id): JsonResponse
    {
        try {
            $batchCodeOrigen = (new \App\Models\BatchCodeOrigen)->findOrFail($id);
            $batchCodeOrigen->update($request->validated());
            return ApiResponse::success($batchCodeOrigen, 'Código modificado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Código no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e, 'Ocurrió un erro', 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $batchCodeOrigen = (new \App\Models\BatchCodeOrigen)->findOrFail($id);
            $batchCodeOrigen->delete();
            return ApiResponse::success($batchCodeOrigen, 'Código eliminado', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Código no encontrado', 500);
        } catch (\Exception $e) {
            return ApiResponse::error($e, 'Ocurrió un erro', 500);
        }
    }
}
