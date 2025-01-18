<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::paginate(10);
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'data' => $user,
            'status' => 200,
        ], 200);
    }


}
