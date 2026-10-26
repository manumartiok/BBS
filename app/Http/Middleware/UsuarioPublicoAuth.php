<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Exige sesión de usuario público; si no hay, manda al registro/login público
class UsuarioPublicoAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('usuario_publico')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Iniciá sesión para usar el carrito.',
                    'redirect' => route('web-acceso'),
                ], 401);
            }

            return redirect()->route('web-acceso');
        }

        return $next($request);
    }
}
