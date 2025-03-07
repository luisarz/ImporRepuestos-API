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
            $users = User::select('id','name','email')->paginate();
            return ApiResponse::success($users, 'Users recuperados existosamente', 200);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }

    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        try {

            $user = User::create($request->validated());
            $roles=$request->get('roles');

            $user->assignRole($roles);
            $response=[
                'user'=>$user,
                'roles'=>$request->get('roles')
            ];
            return ApiResponse::success($response, 'User creado exitosamente', 201);
        }catch (\Exception $e){
            return ApiResponse::error($e->getMessage(),'Ocurrrió un error', 500);
        }

    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrfail($id);
         $user->getRoleNames();
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
            $user=User::findOrfail($id);
            $user->update($request->validated());
            $roles=$request->get('roles');
            $user->syncRoles($roles);
            return ApiResponse::success($user, 'User actualizado existosamente', 200);
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


        return response()->noContent();
    }
}
