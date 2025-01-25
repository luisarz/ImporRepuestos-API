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
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $departments = Department::with('country')->paginate(10);
        return response()->json($departments); //new DepartmentCollection($departments);
    }

    public function store(DepartmentStoreRequest $request): DepartmentResource
    {
        $department = (new \App\Models\Department)->create($request->validated());

        return new DepartmentResource($department);
    }

    public function show(Request $request, Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }

    public function update(DepartmentUpdateRequest $request, Department $department): DepartmentResource
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
