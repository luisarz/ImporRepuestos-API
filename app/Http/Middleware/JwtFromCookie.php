<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtFromCookie
{
    /**
     * Extrae el token JWT de la cookie y lo agrega al header Authorization
     *
     * Este middleware permite que el sistema funcione tanto con:
     * - Tokens en Authorization header (método actual)
     * - Tokens en cookies httpOnly (método seguro)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        // Si no hay token válido en Authorization header, buscar en cookie
        // Considera inválidos: null, "null", vacío
        if ((!$bearerToken || $bearerToken === 'null' || trim($bearerToken) === '')
            && $request->hasCookie('auth_token')) {
            $token = $request->cookie('auth_token');
            $request->headers->set('Authorization', "Bearer {$token}");
        }

        return $next($request);
    }
}
