<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 2 -- XSS. Supplied; leave it alone. A <script> tag in a note must be shown as text,
// not run. The note text should still appear on the page (escaped), so you can't "fix" it
// by hiding the notes.
class Drill2Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_script_in_a_note_is_escaped_not_executed(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
            'notes' => "<script>alert('xss-marker')</script>",
        ]);

        $response = $this->get('/todos/'.$todo->id)->assertOk();

        // The raw script tag must NOT be in the HTML (second arg false = do not escape the needle).
        $response->assertDontSee("<script>alert('xss-marker')</script>", false);
        // The text is still shown, escaped (assertSee escapes the needle, matching {{ }} output).
        $response->assertSee("alert('xss-marker')");
    }
}
