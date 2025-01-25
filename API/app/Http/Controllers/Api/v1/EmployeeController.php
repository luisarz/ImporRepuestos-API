<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EmployeeStoreRequest;
use App\Http\Requests\Api\v1\EmployeeUpdateRequest;
use App\Http\Resources\Api\v1\EmployeeCollection;
use App\Http\Resources\Api\v1\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = Employee::all();

        return new EmployeeCollection($employees);
    }

    public function store(EmployeeStoreRequest $request): Response
    {
        $employee = Employee::create($request->validated());

        return new EmployeeResource($employee);
    }

    public function show(Request $request, Employee $employee): Response
    {
        return new EmployeeResource($employee);
    }

    public function update(EmployeeUpdateRequest $request, Employee $employee): Response
    {
        $employee->update($request->validated());

        return new EmployeeResource($employee);
    }

    public function destroy(Request $request, Employee $employee): Response
    {
        $employee->delete();

        return response()->noContent();
    }
}
