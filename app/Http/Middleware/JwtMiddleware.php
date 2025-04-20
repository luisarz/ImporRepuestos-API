<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $originalToken = JWTAuth::getToken()->get();
            $payload = JWTAuth::getPayload($originalToken);

            $response = $next($request);

            // Verificar si el token necesita ser refrescado (ej. 15 min antes de expirar)
            $shouldRefresh = ($payload['exp'] - time()) < 900;

            if ($shouldRefresh) {
                $newToken = JWTAuth::refresh($originalToken);
//            \Illuminate\Log\log("Original token ".$originalToken);
//                \Illuminate\Log\log("New token ".$newToken);
                $tokenChanged = ($originalToken !== $newToken);

                $responseData = $response->getOriginalContent();
                $responseData['auth_token'] = $newToken;
                $responseData['token_changed'] = $tokenChanged;
                $responseData['token_expires_in'] = $payload['exp'] - time();

                return response()->json($responseData)->withHeaders([
                    'Authorization' => 'Bearer ' . $newToken
                ]);
            }

            // Si no se refrescó, agregamos información del token
            $responseData = $response->getOriginalContent();
            $responseData['token_changed'] = false;
            $responseData['token_expires_in'] = $payload['exp'] - time();

            return response()->json($responseData);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'error' => 'Token expirado',
                'token_changed' => false
            ], 401);

        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return response()->json([
                'error' => 'Token inválido',
                'token_changed' => false
            ], 401);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'error' => 'Token no válido',
                'token_changed' => false
            ], 401);
        }
    }
}