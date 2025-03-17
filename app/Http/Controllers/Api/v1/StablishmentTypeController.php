<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StablishmentTypeStoreRequest;
use App\Http\Requests\Api\v1\StablishmentTypeUpdateRequest;
use App\Http\Resources\Api\v1\StablishmentTypeCollection;
use App\Http\Resources\Api\v1\StablishmentTypeResource;
use App\Models\StablishmentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StablishmentTypeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $establishmentTypes = StablishmentType::paginate($perPage);
            return ApiResponse::success($establishmentTypes, 'Lista de tipos de establecimientos', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null,$e->getMessage(),500);
        }

    }

    public function store(StablishmentTypeStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType = StablishmentType::create($request->validated());
            return ApiResponse::success(new StablishmentTypeResource($establishmentType), 'Tipo de establecimiento creado de manera exitosa!', 201);

        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no creado', 400);
        }

    }

    public function show(Request $request, StablishmentType $establishmentType): \Illuminate\Http\JsonResponse
    {
        try {
          return ApiResponse::success($establishmentType, 'Detalle del tipo de establecimiento', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no encontrado', 404);
        }
    }

    public function update(StablishmentTypeUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType=StablishmentType::find($id);
            if(!$establishmentType){
                return ApiResponse::error(null, 'Tipo de establecimiento no encontrado', 404);
            }
            $establishmentType->update($request->validated());
            return ApiResponse::success(new StablishmentTypeResource($establishmentType), 'Tipo de establecimiento actualizado de manera exitosa', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no actualizado', 400);
        }

    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $establishmentType=StablishmentType::find($id);
            if(!$establishmentType){
                return ApiResponse::error(null, 'Tipo de establecimiento no encontrado', 404);
            }
            $establishmentType->delete();
            return ApiResponse::success(null, 'Tipo de establecimiento eliminado de manera exitosa', 200);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Tipo de establecimiento no eliminado', 400);
        }

    }
}
