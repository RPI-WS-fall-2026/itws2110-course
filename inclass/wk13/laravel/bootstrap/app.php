<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // DRILL 2: the health route goes here.
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Week 11, drill 6: the security headers on every response.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->trustProxies(at: '*');
        // DRILL 4: trust the proxy in front of this container.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
