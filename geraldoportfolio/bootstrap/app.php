<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => EnsureAdminAuthenticated::class,
            'guest.admin' => RedirectIfAdminAuthenticated::class,
        ]);

        // En-tetes de securite sur toutes les reponses (CC §25)
        $middleware->append(SecurityHeaders::class);

        // Derriere un reverse proxy (CC §25)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
