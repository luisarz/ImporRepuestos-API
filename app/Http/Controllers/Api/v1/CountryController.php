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
            $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');

            $query = Country::query();

            // Filtro de búsqueda
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            }

            // Filtro por estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            $countries = $query->paginate($perPage);

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

    public function stats(): \Illuminate\Http\JsonResponse
    {
        try {
            $total = Country::count();
            $active = Country::where('is_active', 1)->count();
            $inactive = Country::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    // Acciones grupales
    public function bulkGet(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            $items = Country::whereIn('id', $ids)->get();

            return ApiResponse::success($items, 'Elementos recuperados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Country::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Elementos activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDeactivate(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Country::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Elementos desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Country::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Elementos eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
