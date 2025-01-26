<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CountryStoreRequest;
use App\Http\Requests\Api\v1\CountryUpdateRequest;
use App\Http\Resources\Api\v1\CountryCollection;
use App\Http\Resources\Api\v1\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CountryController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $countries = (new Country)->paginate(10);
         return ApiResponse::success($countries, 'Países recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar la información', 500);
        }

    }

    public function store(CountryStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $country = (new Country)->create($request->validated());
            return ApiResponse::success(new CountryResource($country), 'País creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al crear el país', 500);
        }
    }

    public function show(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $country = (new Country)->findOrFail($id);
            return ApiResponse::success(new CountryResource($country), 'País recuperado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar la información', 500);
        }
    }

    public function update(CountryUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $country = (new Country)->findOrFail($id);
            $country->update($request->validated());
          return ApiResponse::success(new CountryResource($country), 'País actualizado exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al actualizar el país', 500);
        }
    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $country = (new Country)->findOrFail($id);
            $country->delete();
            return ApiResponse::success(null, 'País eliminado exitosamente', 200);
        } catch (\Exception $e) {
        return ApiResponse::error($e->getMessage(), 'Ocurrió un error al eliminar el País',500);
        }
    }
}
