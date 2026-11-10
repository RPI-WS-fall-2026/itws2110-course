<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 8 -- the rule written once, as a policy. Supplied; leave it alone.
// One to-do of Alex's, and the table in app/Policies/TodoPolicy.php, row by row.
class Drill8Test extends TestCase
{
    use RefreshDatabase;

    public function test_the_policy_decides_who_may_do_what(): void
    {
        $alex = User::factory()->create();
        $priya = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $his = Todo::factory()->for($alex)->create(['title' => 'Alex private to-do', 'done' => false]);

        // The admin: may view, may not update (403).
        $this->actingAs($admin);

        $status = $this->get("/todos/{$his->id}")->status();
        $this->assertSame(200, $status, "The admin opened Alex's to-do from /admin and got $status, not 200. The admin may view any to-do: in findTodo(), find the to-do in the whole table (Todo::findOrFail) and let TodoPolicy::view() decide, with Gate::authorize(\$ability, \$todo).");

        $status = $this->patch("/todos/{$his->id}/toggle")->status();
        $this->assertSame(403, $status, "The admin pressed Mark done on Alex's to-do: PATCH .../toggle answered $status, not 403. TodoPolicy::update() should deny the admin with Response::deny(), and findTodo() must ask the policy.");
        $this->assertFalse($his->fresh()->done, "The admin changed Alex's to-do. Only the owner may update it.");

        // Anyone else: 404 for all three, the same answer drill 6 gave.
        $this->actingAs($priya);
        foreach ([['GET', "/todos/{$his->id}", 'view'], ['PATCH', "/todos/{$his->id}/toggle", 'update'], ['DELETE', "/todos/{$his->id}", 'delete']] as [$method, $uri, $ability]) {
            $status = $this->call($method, $uri)->status();
            $this->assertSame(404, $status, "Priya sent $method $uri and got $status, not 404. In TodoPolicy::$ability(), someone who is neither the owner nor allowed as the admin gets Response::denyAsNotFound().");
        }
        $this->assertNotNull($his->fresh(), 'Priya deleted Alex\'s to-do.');
        $this->assertFalse($his->fresh()->done, 'Priya changed Alex\'s to-do.');

        // The owner: may update.
        $this->actingAs($alex);
        $status = $this->patch("/todos/{$his->id}/toggle")->status();
        $this->assertSame(302, $status, "Alex marked his own to-do done and got $status. The owner may update: TodoPolicy::update() must allow him.");
        $this->assertTrue($his->fresh()->done, 'Alex pressed Mark done on his own to-do and it did not change.');

        // The admin: may delete (last, because it removes the row).
        $this->actingAs($admin);
        $status = $this->delete("/todos/{$his->id}")->status();
        $this->assertSame(302, $status, "The admin deleted Alex's to-do and got $status. TodoPolicy::delete() should allow the owner or the admin.");
        $this->assertNull($his->fresh(), "The admin's delete answered 302 but the to-do is still there.");
    }
}
