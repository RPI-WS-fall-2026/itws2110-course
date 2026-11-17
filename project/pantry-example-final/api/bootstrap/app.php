<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum SPA authentication: requests from the React app get a session and CSRF
        // protection, like a Blade form does (session 17).
        $middleware->statefulApi();
        // Deployed, TLS ends at Azure's edge and the request reaches this container as plain
        // http. Trusting the proxy's headers lets Laravel know the visitor used https, so
        // links and the Secure session cookie come out right. Deployment, 11/17.
        $middleware->trustProxies(at: '*');
        // The four security headers from week 11, on every response. One of them, HSTS, is
        // what makes a browser refuse plain http for this site from now on.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // The error bodies from the midterm design (PROJECT.pptx, section C).
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/items/*') && $e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json(['message' => 'No item with id '.$request->route('item').'.'], 404);
            }
        });
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/items/*') && $e->getPrevious() instanceof AuthorizationException) {
                $message = $request->isMethod('DELETE')
                    ? 'Only the RA can remove items from the shelf.'
                    : 'Only the owner or the RA can change this item.';
                return response()->json(['message' => $message], 403);
            }
        });
    })->create();
