<?php

namespace App\Http\Middleware;

use App\Support\Idiomas;
use Closure;
use Illuminate\Http\Request;

/**
 * Aplica el idioma elegido por el visitante (guardado en sesión)
 * o el idioma principal de config/sitio.php.
 */
class SetIdioma
{
    public function handle(Request $request, Closure $next)
    {
        $idioma = session('idioma');

        if (!in_array($idioma, Idiomas::todos())) {
            $idioma = Idiomas::principal();
        }

        app()->setLocale($idioma);

        return $next($request);
    }
}
