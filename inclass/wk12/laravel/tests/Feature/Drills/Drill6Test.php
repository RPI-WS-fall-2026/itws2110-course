<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 6 -- someone else's to-do, by its number. Supplied; leave it alone.
class Drill6Test extends TestCase
{
    use RefreshDatabase;

    public function test_another_users_todo_is_not_found(): void
    {
        $alex = User::factory()->create();
        $priya = User::factory()->create();
        $his = Todo::factory()->for($alex)->create(['title' => 'Alex private to-do', 'done' => false]);
        $hers = Todo::factory()->for($priya)->create(['title' => 'Priya own to-do']);

        $this->actingAs($priya);

        $this->get("/todos/{$hers->id}")->assertOk();   // her own still works

        $status = $this->get("/todos/{$his->id}")->status();
        $this->assertSame(404, $status, "Priya opened Alex's to-do: GET /todos/{id} answered $status, not 404. Look the to-do up through \$request->user()->todos() in findTodo().");

        $status = $this->patch("/todos/{$his->id}/toggle")->status();
        $this->assertSame(404, $status, "Priya marked Alex's to-do done: PATCH .../toggle answered $status, not 404.");
        $this->assertFalse($his->fresh()->done, "Alex's to-do was changed by Priya.");

        $status = $this->delete("/todos/{$his->id}")->status();
        $this->assertSame(404, $status, "Priya deleted Alex's to-do: DELETE /todos/{id} answered $status, not 404.");
        $this->assertNotNull($his->fresh(), "Alex's to-do was deleted by Priya.");
    }
}
