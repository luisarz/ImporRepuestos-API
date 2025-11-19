<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\MenuAllowedRequest;
use App\Models\Employee;
use App\Models\ModuleRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
//    public function register(LoginRequest $request): JsonResponse
//    {
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|string|max:255',
//            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|string|min:6|confirmed',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json($validator->errors()->toJson(), 400);
//        }
//
//        $user = (new User)->create([
//            'name' => $request->get('name'),
//            'email' => $request->get('email'),
//            'password' => Hash::make($request->get('password')),
//        ]);
//
//        $token = JWTAuth::fromUser($user);
//
//        return response()->json(compact('user', 'token'), 201);
//    }

    // User login
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        try {
            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get authenticated user


    // User logout
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        // Eliminar la cookie del token
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Successfully logged out'])->cookie($cookie);
    }

    protected function respondWithToken($token)
    {
        $user = auth()->user();

        $expiresIn = auth()->factory()->getTTL(); // En minutos

        // Obtener información del empleado si existe
        $employee = null;
        $warehouseId = null;
        $employeeId = null;
        $employeeName = $user->name;
        $warehouseName = null;

        if ($user->employee_id) {
            try {
                $employee = Employee::with('warehouse')->findOrFail($user->employee_id);
                $warehouseId = $employee->warehouse_id;
                $employeeId = $employee->id;
                $employeeName = $employee->name . ' ' . $employee->last_name;
                $warehouseName = $employee->warehouse->name;
            } catch (\Exception $e) {
                // Si no se encuentra el empleado, continuar sin datos de empleado
            }
        }

        // Obtener roles y permisos del usuario (Spatie Permission)
        $roles = $user->roles->pluck('name');
        $permissions = $user->getAllPermissions()->pluck('name');

        // ✅ Crear cookie httpOnly con el token (SEGURIDAD MEJORADA)
        $cookie = $this->createAuthCookie($token, $expiresIn);

        return response()->json([
            'logged_status' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'roles' => $roles,
            'permissions' => $permissions,
            'warehouse_id' => $warehouseId,
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'warehouse_name' => $warehouseName,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn
        ])->cookie($cookie);
    }

    private function getMenu(MenuAllowedRequest $request)
    {
        $empleados = Employee::findOrFail($user->id_empleado_usuario);
        $session = session();
        $session->put('id', $user->id);
        $session->put('id_empleado_usuario', $user->id_empleado_usuario);
        $session->put('email', $user->email);
        $session->put('name', $empleados->nombre_empleado);
        $session->put('id_rol', $user->id_rol);


        $Access = ModuleRol::Where("id_rol", $user->id_rol)
            ->join('modulo', 'modulo.id_modulo', '=', 'modulo_rol.id_modulo')
            ->orderBy('modulo.orden', 'ASC')->get();

        $session->put("access", $Access);

        \Illuminate\Support\Facades\Auth::login($user, true);
    }

    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth()->refresh();
            $expiresIn = auth()->factory()->getTTL();

            // ✅ NUEVO: Crear nueva cookie con token refrescado
            $cookie = $this->createAuthCookie($newToken, $expiresIn);

            return response()->json([
                'token' => $newToken,  // ✅ Aún enviamos en JSON para compatibilidad
                'token_type' => 'bearer',
                'expires_in' => $expiresIn,
                'message' => 'Token refrescado exitosamente'
            ])->cookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo refrescar el token'
            ], 401);
        }
    }

    /**
     * ✅ NUEVO: Obtener información del usuario autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }

    /**
     * ✅ NUEVO: Crear cookie segura para el token
     * Configuración dinámica según ambiente
     */
    private function createAuthCookie(string $token, int $expiresIn)
    {
        return cookie(
            'auth_token',                           // nombre
            $token,                                 // valor
            $expiresIn,                            // duración en minutos
            '/',                                    // path
            null,                                   // domain (null = dominio actual, permite localhost)
            config('app.env') === 'production',    // secure (solo HTTPS en producción)
            true,                                   // httpOnly (NO accesible a JavaScript) ✅ SEGURIDAD
            false,                                  // raw
            config('app.env') === 'production' ? 'strict' : 'lax'  // sameSite: lax en dev, strict en prod
        );
    }
}
