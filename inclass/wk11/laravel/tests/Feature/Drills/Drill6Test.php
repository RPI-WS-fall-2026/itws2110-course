<?php

namespace Tests\Feature\Drills;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// Drill 6 -- security headers. Supplied; leave it alone. Every response must carry the four
// headers: the to-do list (step 3 puts the middleware on that one route) and the drills page
// (step 4 appends it in bootstrap/app.php, so it runs on every route).
class Drill6Test extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_carries_the_four_security_headers(): void
    {
        $this->assertFourHeaders($this->get('/todos'), '/todos', '');
        $this->assertFourHeaders($this->get('/'), '/',
            ' /todos has it, so the middleware is on one route only (step 3). Step 4: take it off the route and append it in bootstrap/app.php.');
    }

    private function assertFourHeaders(TestResponse $response, string $url, string $hint): void
    {
        $response->assertOk();

        foreach (['X-Content-Type-Options' => 'nosniff', 'X-Frame-Options' => 'DENY'] as $name => $value) {
            $this->assertSame($value, $response->headers->get($name),
                "No {$name}: {$value} header on {$url}.".$hint);
        }
        foreach (['Content-Security-Policy', 'Strict-Transport-Security'] as $name) {
            $this->assertNotEmpty($response->headers->get($name), "No {$name} header on {$url}.".$hint);
        }
    }
}
