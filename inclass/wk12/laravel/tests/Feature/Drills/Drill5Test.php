<?php

namespace Tests\Feature\Drills;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 5 -- register as an admin. Supplied; leave it alone. Both fixes are checked.
class Drill5Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_role_sent_with_the_registration_is_ignored(): void
    {
        $this->post('/register', [
            'name' => 'Mallory', 'email' => 'mallory@example.com',
            'password' => 'mallory-pass', 'password_confirmation' => 'mallory-pass',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'mallory@example.com')->first();
        $this->assertNotNull($user, 'Registering did not create the account at all.');
        $this->assertSame('user', $user->fresh()->role,
            "A registration that sent role=admin made an admin. Create the user from what validate() returns, not \$request->all().");
    }

    public function test_role_is_not_on_the_fillable_list(): void
    {
        $this->assertNotContains('role', (new User)->getFillable(),
            "'role' is still in #[Fillable] in app/Models/User.php, so any array passed to User::create() can set it. Take it off the list.");
    }
}
