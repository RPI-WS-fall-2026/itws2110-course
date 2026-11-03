<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 1 -- SQL injection. Supplied; leave it alone. A crafted search must NOT be able to
// read the users table, and an ordinary search must still work.
class Drill1Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_union_payload_cannot_leak_a_user_email(): void
    {
        $victim = User::factory()->create(['email' => 'victim-secret@example.edu']);
        Todo::factory()->create(['user_id' => $victim->id, 'title' => 'Buy milk at store']);

        // The classic attack: break out of the LIKE string and UNION in the users table.
        // A balanced-quote UNION: it closes the LIKE string and unions in the users table,
        // needing no comment -- so it works the same on MySQL (the pages) and SQLite (this check).
        $payload = "' UNION SELECT id, email, 0 FROM users WHERE email LIKE '%";

        $this->get('/todos?q='.urlencode($payload))
            ->assertOk()
            ->assertDontSee('victim-secret@example.edu');
    }

    public function test_an_ordinary_search_still_finds_a_matching_title(): void
    {
        $user = User::factory()->create();
        Todo::factory()->create(['user_id' => $user->id, 'title' => 'Buy milk at store']);
        Todo::factory()->create(['user_id' => $user->id, 'title' => 'Return books']);

        $this->get('/todos?q='.urlencode('milk'))
            ->assertOk()
            ->assertSee('Buy milk at store')
            ->assertDontSee('Return books');
    }
}
