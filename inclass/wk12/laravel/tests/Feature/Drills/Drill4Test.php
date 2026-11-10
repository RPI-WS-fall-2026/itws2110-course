<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 4 -- a route outside the login. Supplied; leave it alone.
class Drill4Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_who_is_not_logged_in_cannot_delete_a_todo(): void
    {
        $todo = Todo::factory()->create(['title' => 'Do not delete me']);

        $response = $this->delete('/todos/'.$todo->id);   // no actingAs(): nobody is logged in

        $this->assertTrue(Todo::whereKey($todo->id)->exists(),
            'A visitor who never logged in deleted a to-do with DELETE /todos/{id}. Move that route inside the auth group in routes/web.php.');
        $response->assertRedirect('/login');
    }
}
