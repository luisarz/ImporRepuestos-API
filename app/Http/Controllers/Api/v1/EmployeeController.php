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
            $perPage = $request->input('per_page', 10);

            $employees = Employee::with('warehouse:id,name,phone,email', 'district:id,code,description')->paginate($perPage);
            return ApiResponse::success($employees, 'Empleados recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function store(EmployeeStoreRequest $request): JsonResponse
    {
        try {
            $employee = (new \App\Models\Employee)->create($request->validated());
            return ApiResponse::success(new EmployeeResource($employee), 'Empleado creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $employee = Employee::with('warehouse:id,name,phone,email', 'district:id,code,description')->findOrFail($id);
            return ApiResponse::success($employee, 'Empleado recuperado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Empleado no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function update(EmployeeUpdateRequest $request, $id): JsonResponse
    {
        try {
            $employee = (new Employee)->findOrFail($id);
            $employee->update($request->validated());
            return ApiResponse::success(new EmployeeResource($employee), 'Empleado actualizado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 'Empleado no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $employee = (new Employee)->findOrFail($id);
            $employee->delete();
            return ApiResponse::success(null, 'Empleado eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
           return ApiResponse::error(null, 'Empleado no encontrado', 404);
        }catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }

    }

    /**
     * Obtener todos los empleados activos (sin paginar)
     * Para uso en selects y formularios
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $employees = Employee::where('is_active', 1)
                ->select('id', 'name', 'last_name', 'email')
                ->orderBy('name')
                ->get();
            return ApiResponse::success($employees, 'Empleados recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
