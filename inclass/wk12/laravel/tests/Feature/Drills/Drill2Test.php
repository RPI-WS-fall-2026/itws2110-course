<?php

namespace Tests\Feature\Drills;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 2 -- two error messages. Supplied; leave it alone.
class Drill2Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_wrong_password_and_an_unknown_email_get_the_same_message(): void
    {
        User::factory()->create(['email' => 'known@example.edu']);

        // Read the message off the login page the browser is sent back to: what a stranger sees.
        $unknown = $this->messageAfter(['email' => 'nobody@example.edu', 'password' => 'whatever-1']);
        $wrong = $this->messageAfter(['email' => 'known@example.edu', 'password' => 'whatever-1']);

        $this->assertNotEmpty($unknown, 'Logging in with an unknown email showed no error message under the email field.');
        $this->assertNotEmpty($wrong, 'Logging in with a wrong password showed no error message under the email field.');
        $this->assertSame($unknown, $wrong,
            "Two different messages: \"$unknown\" for an unknown email, \"$wrong\" for a wrong password. Use one message for both.");
    }

    private function messageAfter(array $form): string
    {
        $html = $this->followingRedirects()->from('/login')->post('/login', $form)->getContent();

        return preg_match('/<p class="error"[^>]*>(.*?)<\/p>/s', $html, $m) ? trim(html_entity_decode($m[1])) : '';
    }
}
