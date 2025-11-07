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
        $employee = Employee::with('warehouse')->findOrFail($user->employee_id);

        $expiresIn = auth()->factory()->getTTL(); // En minutos

        // Crear cookie httpOnly con el token
        $cookie = cookie(
            'auth_token',           // nombre
            $token,                 // valor
            $expiresIn,            // tiempo en minutos
            '/',                    // path
            null,                   // domain (null = dominio actual)
            false,                  // secure (true en producción con HTTPS)
            true,                   // httpOnly (no accesible desde JS)
            false,                  // raw
            'lax'                   // sameSite (lax o strict)
        );

        return response()->json([
            'logged_status' => true,
            'warehouse_id' => $employee->warehouse_id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name . ' ' . $employee->last_name,
            'warehouse_name' => $employee->warehouse->name,
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
        return $this->respondWithToken(auth()->refresh());
    }
}
