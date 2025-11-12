<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Autenticar usando el token del header Authorization
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'User associated with token not found.'
                ], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // Log para diagnosticar
            \Log::info('Token expirado detectado', [
                'path' => $request->path(),
                'url' => $request->url(),
                'is_refresh_v1' => $request->is('api/v1/refresh'),
                'is_refresh' => $request->is('refresh')
            ]);

            // Permitir tokens expirados SOLO en la ruta de refresh
            if ($request->is('api/v1/refresh') || $request->is('refresh')) {
                // El token está expirado pero es válido para refresh
                // JWT permite refrescar tokens expirados automáticamente
                \Log::info('Permitiendo token expirado en ruta de refresh');
                return $next($request);
            }

            \Log::warning('Token expirado rechazado - ruta no es refresh');
            return response()->json([
                'error' => 'Token expired',
                'message' => 'Your session has expired. Please login again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'error' => 'Token invalid',
                'message' => 'Invalid token. Please login again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'error' => 'Token not provided',
                'message' => 'Authorization token not found. Please login again.'
            ], 401);
        }

        return $next($request);
    }
}