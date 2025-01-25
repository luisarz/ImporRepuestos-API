<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DepartmentStoreRequest;
use App\Http\Requests\Api\v1\DepartmentUpdateRequest;
use App\Http\Resources\Api\v1\DepartmentCollection;
use App\Http\Resources\Api\v1\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $departments = Department::all();

        return new DepartmentCollection($departments);
    }

    public function store(DepartmentStoreRequest $request): Response
    {
        $department = Department::create($request->validated());

        return new DepartmentResource($department);
    }

    public function show(Request $request, Department $department): Response
    {
        return new DepartmentResource($department);
    }

    public function update(DepartmentUpdateRequest $request, Department $department): Response
    {
        $department->update($request->validated());

        return new DepartmentResource($department);
    }

    public function destroy(Request $request, Department $department): Response
    {
        $department->delete();

        return response()->noContent();
    }
}
