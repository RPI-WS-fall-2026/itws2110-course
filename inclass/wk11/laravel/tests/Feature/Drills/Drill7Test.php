<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 7 -- rate limiting. Supplied; leave it alone. Twenty searches in a minute from one
// address are fine; the twenty-first is refused with 429 Too Many Requests, by the throttle
// middleware, before the controller ever runs.
class Drill7Test extends TestCase
{
    use RefreshDatabase;

    public function test_the_twenty_first_search_in_a_minute_is_refused(): void
    {
        $user = User::factory()->create();
        Todo::factory()->create(['user_id' => $user->id, 'title' => 'Buy rice']);

        for ($i = 1; $i <= 20; $i++) {
            $status = $this->get('/todos?q=rice')->getStatusCode();
            $this->assertSame(200, $status,
                "Search {$i} of 20 was answered {$status}. The first twenty searches in a minute must still work: the limit is 20 per minute.");
        }

        $response = $this->get('/todos?q=rice');
        $this->assertSame(429, $response->getStatusCode(),
            'Search 21 in the same minute was answered '.$response->getStatusCode().', not 429 Too Many Requests: nothing is slowing a guesser down. '.
            "Define the 'search' limiter in AppServiceProvider::boot() and put throttle:search on the todos.index route.");
        $this->assertNotEmpty($response->headers->get('Retry-After'), 'The 429 should carry a Retry-After header saying when to try again.');
    }
}
