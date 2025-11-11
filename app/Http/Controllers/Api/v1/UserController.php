<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UserStoreRequest;
use App\Http\Requests\Api\v1\UserUpdateRequest;
use App\Http\Resources\Api\v1\UserCollection;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10); // Si no envía per_page, usa 10 por defecto
            $search = $request->input('search', '');
            $statusFilter = $request->input('status_filter', '');
            // El DataTable envía 'sortField' y 'sortOrder'
            $sortBy = $request->input('sortField', 'id');
            $sortOrderRaw = $request->input('sortOrder', 'asc');

            // Convertir a minúsculas y validar
            $sortOrder = strtolower($sortOrderRaw);
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc'; // Valor por defecto si no es válido
            }

            $query = User::with(['employee', 'roles']);

            // Búsqueda por múltiples campos
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('employee', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('dui', 'like', "%{$search}%")
                            ->orWhere('nit', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            // Aplicar filtro de estado si se proporciona
            if ($statusFilter !== '') {
                $query->where('is_active', $statusFilter);
            }

            // Aplicar ordenamiento - solo campos propios del modelo
            $allowedSortFields = ['id', 'name', 'email', 'is_active', 'created_at', 'updated_at'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $users = $query->paginate($perPage);
            return ApiResponse::success($users, 'Users recuperados existosamente', 200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }

    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Hashear el password
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }

            $user = User::create($data);
            $roles = $request->get('roles');

            $user->assignRole($roles);

            $response = [
                'user' => $user->load(['employee', 'roles']),
                'roles' => $request->get('roles')
            ];

            return ApiResponse::success($response, 'User creado exitosamente', 201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = User::with(['employee', 'roles'])->findOrfail($id);
            return ApiResponse::success($user, 'User recuperado existosamente', 200 );
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'User no encontrado', 404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }
    }

    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        try {
            $user = User::findOrfail($id);
            $data = $request->validated();

            // Solo actualizar password si se envió uno nuevo
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                // No actualizar el password si está vacío
                unset($data['password']);
            }

            $user->update($data);

            $roles = $request->get('roles');
            $user->syncRoles($roles);

            return ApiResponse::success($user->load(['employee', 'roles']), 'User actualizado existosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'User no encontrado', 404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }
    }

    public function destroy(Request $request,$id): JsonResponse
    {
        try {
            $user=User::findOrfail($id);
            $user->syncRoles([]);
            $user->delete();
            return ApiResponse::success(null, 'User eliminado existosamente', 200);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error(null, 'User no encontrado', 404);
        }catch(\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }
    }

    /**
     * Obtener estadísticas de los usuarios
     */
    public function stats(): JsonResponse
    {
        try {
            $total = User::count();
            $active = User::where('is_active', 1)->count();
            $inactive = User::where('is_active', 0)->count();

            $stats = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive
            ];

            return ApiResponse::success($stats, 'Estadísticas recuperadas exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Obtener usuarios por IDs (para exportación)
     */
    public function bulkGet(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $users = User::with(['employee', 'roles'])->whereIn('id', $ids)->get();
            return ApiResponse::success($users, 'Usuarios recuperados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Activar múltiples usuarios
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            User::whereIn('id', $ids)->update(['is_active' => 1]);
            return ApiResponse::success(null, 'Usuarios activados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Desactivar múltiples usuarios
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            User::whereIn('id', $ids)->update(['is_active' => 0]);
            return ApiResponse::success(null, 'Usuarios desactivados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }

    /**
     * Eliminar múltiples usuarios
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);
            $users = User::whereIn('id', $ids)->get();

            foreach ($users as $user) {
                $user->syncRoles([]);
                $user->delete();
            }

            return ApiResponse::success(null, 'Usuarios eliminados exitosamente', 200);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Ocurrió un error', 500);
        }
    }
}
