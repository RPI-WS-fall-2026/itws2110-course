<?php

namespace Tests\Feature\Drills;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 3 -- CSRF. Supplied; leave it alone. The add-to-do form must carry a CSRF token
// (@csrf), and bootstrap/app.php must no longer exclude /todos from the CSRF check.
//
// Note: Laravel's test runner skips the live CSRF middleware, so this check looks at the two
// things your fix changes: the token in the form, and the exclusion in bootstrap/app.php.
class Drill3Test extends TestCase
{
    use RefreshDatabase;

    public function test_the_add_form_includes_a_csrf_token(): void
    {
        User::factory()->create();   // user 1, so the page renders

        $this->get('/todos')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_the_todos_route_is_not_excluded_from_csrf(): void
    {
        $src = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertDoesNotMatchRegularExpression(
            '/validateCsrfTokens\s*\(\s*except:\s*\[[^\]]*todos/',
            $src,
            "bootstrap/app.php still excludes 'todos' from the CSRF check -- delete that line."
        );
    }
}
