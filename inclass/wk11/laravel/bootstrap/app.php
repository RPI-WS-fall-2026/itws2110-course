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
        // DRILL 3: this line turns OFF Laravel's CSRF protection for the /todos route, which
        // is why the attacker page at /attack can post to it. Delete this line.
        $middleware->validateCsrfTokens(except: ['todos']);

        // DRILL 6: once you have written app/Http/Middleware/SecurityHeaders.php, register it
        // here so it runs on every response:
        // $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
