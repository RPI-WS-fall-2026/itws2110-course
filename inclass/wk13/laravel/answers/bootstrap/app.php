<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Worked answer. Drills 2 and 4.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // DRILL 2 fixed: GET /up answers 200 once the app has started. The platform asks it
        // before it sends visitors to a new copy.
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Week 11, drill 6: the security headers on every response.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // DRILL 4 fixed: believe the X-Forwarded-* headers. The proxy holds the certificate
        // and speaks https to the browser; it tells this container so in those headers.
        // '*' is right only because nothing but the proxy can reach the container.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
