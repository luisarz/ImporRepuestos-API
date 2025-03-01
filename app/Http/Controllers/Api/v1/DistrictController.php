<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DistrictStoreRequest;
use App\Http\Requests\Api\v1\DistrictUpdateRequest;
use App\Http\Resources\Api\v1\DistrictCollection;
use App\Http\Resources\Api\v1\DistrictResource;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $districts = District::with('municipality')->paginate(10);
        return ApiResponse::success($districts, 'Districts retrieved successfully.', 200);

    }

    public function store(DistrictStoreRequest $request): JsonResponse
    {
        try {
            $district = (new District)->create($request->validated());
            return ApiResponse::success(new DistrictResource($district), 'District created successfully.', 201);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function show(Request $request,$id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);

            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            return ApiResponse::success(new DistrictResource($district), 'Distrito encontrado', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }
    }

    public function update(DistrictUpdateRequest $request, $id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);
            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            $district->update($request->validated());
            return ApiResponse::success(new DistrictResource($district), 'Distrito actualizado correctamente.', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $district = (new District)->findOrFail($id);
            if(!$district){
                return ApiResponse::error(null, 'Distrito no encontrado.', 404);
            }
            $district->delete();
            return ApiResponse::success(null, 'Distrito eliminado correctamente.', 200);
        }catch (\Exception $e){
            return ApiResponse::error(null, $e->getMessage(), 500);
        }


    }
}
