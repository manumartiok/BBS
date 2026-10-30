<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar alias de middlewares
        $middleware->alias([
            'web.usuario.auth' => \App\Http\Middleware\WebUsuarioAuth::class,
            'usuario.publico.auth' => \App\Http\Middleware\UsuarioPublicoAuth::class,
        ]);

        // Mercado Pago no manda token CSRF en sus notificaciones
        $middleware->validateCsrfTokens(except: ['webhooks/mercadopago']);

        // Idioma del sitio (config/sitio.php)
        $middleware->web(append: [
            \App\Http\Middleware\SetIdioma::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
