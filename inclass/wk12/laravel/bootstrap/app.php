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
        // Week 11, drill 6: the security headers on every response. (Week 11's CSRF fix is
        // here too: there is no validateCsrfTokens(except: ...) line.)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
