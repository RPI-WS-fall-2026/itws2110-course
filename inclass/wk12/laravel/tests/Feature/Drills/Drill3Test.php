<?php

namespace Tests\Feature\Drills;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 3 -- unlimited guesses. Supplied; leave it alone. Five wrong passwords for one email
// are answered; the sixth in the same minute gets 429.
class Drill3Test extends TestCase
{
    use RefreshDatabase;

    public function test_the_sixth_wrong_password_in_a_minute_gets_429(): void
    {
        User::factory()->create(['email' => 'target@example.edu']);

        for ($i = 1; $i <= 5; $i++) {
            $status = $this->post('/login', ['email' => 'target@example.edu', 'password' => "guess-$i"])->status();
            $this->assertNotSame(429, $status, "Try $i of 5 was already refused with 429. The limit is five a minute: Limit::perMinute(5).");
        }

        $sixth = $this->post('/login', ['email' => 'target@example.edu', 'password' => 'guess-6'])->status();
        $this->assertSame(429, $sixth,
            "The sixth wrong password in a minute was answered with $sixth, not 429 Too Many Requests. Name the 'login' limit in AppServiceProvider and put ->middleware('throttle:login') on POST /login.");
    }
}
