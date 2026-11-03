<?php

namespace Tests\Feature\Drills;

use Tests\TestCase;

// Drill 6 -- security headers. Supplied; leave it alone. Every response must carry the four
// headers, set by app/Http/Middleware/SecurityHeaders.php once it is registered in
// bootstrap/app.php.
class Drill6Test extends TestCase
{
    public function test_every_response_carries_the_four_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');

        $this->assertNotEmpty(
            $response->headers->get('Content-Security-Policy'),
            'No Content-Security-Policy header on the response.'
        );
        $this->assertNotEmpty(
            $response->headers->get('Strict-Transport-Security'),
            'No Strict-Transport-Security header on the response.'
        );
    }
}
