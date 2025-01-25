<?php

namespace App\Http\Controllers\Api\v1;

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
    public function index(Request $request): Response
    {
        $companies = Company::all();

        return new CompanyCollection($companies);
    }

    public function store(CompanyStoreRequest $request): Response
    {
        $company = Company::create($request->validated());

        return new CompanyResource($company);
    }

    public function show(Request $request, Company $company): Response
    {
        return new CompanyResource($company);
    }

    public function update(CompanyUpdateRequest $request, Company $company): Response
    {
        $company->update($request->validated());

        return new CompanyResource($company);
    }

    public function destroy(Request $request, Company $company): Response
    {
        $company->delete();

        return response()->noContent();
    }
}
