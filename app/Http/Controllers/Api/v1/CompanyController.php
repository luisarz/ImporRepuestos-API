<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CompanyStoreRequest;
use App\Http\Requests\Api\v1\CompanyUpdateRequest;
use App\Http\Resources\Api\v1\CompanyCollection;
use App\Http\Resources\Api\v1\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function index(Request $request): CompanyCollection
    {
        $companies = Company::all();
        return new CompanyCollection($companies);
    }

    public function store(CompanyStoreRequest $request): CompanyResource
    {
        $company = (new Company)->create($request->validated());
        return new CompanyResource($company);
    }

    public function show(Request $request, Company $company): CompanyResource
    {
        return new CompanyResource($company);
    }

    public function update(CompanyUpdateRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $company = Company::find($id);
            if (!$company) {
                return ApiResponse::error(null, 'Empresa no encontrada', 404);
            }
            $company->update($request->validated());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Empresa no actualizada', 400);
        }

    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $company = Company::find($id);
            if (!$company) {
                return ApiResponse::error(null, 'Empresa no encontrada', 404);
            }
            $company->delete();
            return ApiResponse::success(null, 'Empresa eliminada de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Empresa no eliminada', 400);
        }
    }
}
