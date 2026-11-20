<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Week 11, drill 6, carried into production: four headers, four refusals, on every response.
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        // Everything the built React app loads (scripts, styles, fonts) comes from this origin.
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        // HSTS: "for the next year, only ever use https for this site". A browser obeys it
        // only when it arrives over https, so on http://localhost it is sent and ignored.
        // Deployed, it closes the gap TLS leaves: the first, plain-http request.
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
