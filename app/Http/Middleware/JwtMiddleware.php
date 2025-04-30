<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
//        try {
//            $user = JWTAuth::parseToken()->authenticate();
//            if (!$user) {
//                return response()->json(['error' => 'User not found'], 401);
//            }
//        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//            return response()->json(['error' => 'Token expired'], 401);
//        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//            return response()->json(['error' => 'Token invalid'], 401);
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
//            return response()->json(['error' => 'Token absent or other error'], 401);
//        }

        return $next($request);
    }
}