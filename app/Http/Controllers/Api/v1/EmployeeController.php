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
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            $query = Employee::with(['jobTitle:id,code,description', 'warehouse:id,name']);

            // Búsqueda
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('dui', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filtro de estado
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Ordenamiento
            $allowedSortFields = ['id', 'name', 'last_name', 'dui', 'email', 'phone', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $employees = $query->paginate($perPage);
            return ApiResponse::success($employees, 'Empleados recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    public function store(EmployeeStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Manejar la carga de imagen
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('employees/photos', $filename, 'public');

                $data['photo'] = [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename
                ];
            }

            $employee = (new \App\Models\Employee)->create($data);
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
            $data = $request->validated();

            // Manejar la carga de imagen
            if ($request->hasFile('photo')) {
                // Eliminar imagen anterior si existe
                if ($employee->photo && isset($employee->photo['path'])) {
                    \Storage::disk('public')->delete($employee->photo['path']);
                }

                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('employees/photos', $filename, 'public');

                $data['photo'] = [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename
                ];
            }

            $employee->update($data);
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

    /**
     * Obtener estadísticas de empleados
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Employee::count();
            $active = Employee::where('is_active', 1)->count();
            $inactive = Employee::where('is_active', 0)->count();

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

    /**
     * Activar múltiples empleados
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Employee::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Empleados activados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    /**
     * Desactivar múltiples empleados
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Employee::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Empleados desactivados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }

    /**
     * Eliminar múltiples empleados
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            Employee::whereIn('id', $ids)->delete();
            return ApiResponse::success(null, 'Empleados eliminados de manera exitosa', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(),'Ocurrió un error', 500);
        }
    }
}
