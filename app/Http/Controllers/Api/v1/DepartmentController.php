<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\DepartmentStoreRequest;
use App\Http\Requests\Api\v1\DepartmentUpdateRequest;
use App\Http\Resources\Api\v1\DepartmentCollection;
use App\Http\Resources\Api\v1\DepartmentResource;
use App\Models\Department;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $departments = Department::with('country')->paginate(10);
            return ApiResponse::success($departments, 'Departamentos recuperados exitosamente', 200);
        }catch (\Exception $e){
           return ApiResponse::error($e->getMessage(),'Ocurrió un error al recuperar la información', 500);
        }
    }

    public function store(DepartmentStoreRequest $request): JsonResponse
    {
        try {
            $department = (new Department)->create($request->validated());
            return ApiResponse::success(new DepartmentResource($department), 'Departamento creado exitosamente', 201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrió un error al crear el departamento', 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $department = Department::with('country')->findOrFail($id);
            return ApiResponse::success(new DepartmentResource($department),'Departamento recuperado exitosamente.', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al recuperar la información.', 500);
        }
    }


    public function update(DepartmentUpdateRequest $request, $id): JsonResponse
    {
        try {
            $department = (new Department)->findOrFail($id);
            $department->update($request->validated());
          return ApiResponse::success(new DepartmentResource($department), 'Departamento actualizado exitosamente.', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al actualizar el departamento.', 500);
        }


    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $department = (new Department)->findOrFail($id);
            $department->delete();
            return ApiResponse::success(null,'Departamento eliminado exitosamente.', 204);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'Departamento no encontrado.', 404);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error al eliminar el departamento.', 500);
        }
    }
}
