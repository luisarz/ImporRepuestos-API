<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\EmployeeStoreRequest;
use App\Http\Requests\Api\v1\EmployeeUpdateRequest;
use App\Http\Resources\Api\v1\EmployeeCollection;
use App\Http\Resources\Api\v1\EmployeeResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $employees = Employee::paginate(10);
            return ApiResponse::success(new EmployeeCollection($employees), 'Empleados recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }

    }

    public function store(EmployeeStoreRequest $request): JsonResponse
    {
        try {
            $employee = (new \App\Models\Employee)->create($request->validated());
            return ApiResponse::success(new EmployeeResource($employee), 'Empleado creado exitosamente', 201);
        }
        catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
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
