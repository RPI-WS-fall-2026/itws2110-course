<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// DRILL 6: add four security headers to every response, then register this middleware in
// bootstrap/app.php so it actually runs. Right now it does nothing but pass the response
// through. Each header tells the browser to refuse one kind of attack -- the four MDN
// guides in this week's reading are exactly these four. Fill in the TODOs.
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // TODO drill 6: set these four headers on $response.
        //   X-Content-Type-Options: nosniff
        //   X-Frame-Options: DENY
        //   Content-Security-Policy: default-src 'self'
        //   Strict-Transport-Security: max-age=31536000; includeSubDomains
        //
        // $response->headers->set('X-Content-Type-Options', 'nosniff');
        // ... (the other three)

        return $response;
    }
}
