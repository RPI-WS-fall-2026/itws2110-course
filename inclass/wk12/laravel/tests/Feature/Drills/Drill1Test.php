<?php

namespace Tests\Feature\Drills;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Drill 1 -- passwords stored as typed. Supplied; leave it alone.
class Drill1Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_password_is_stored_as_a_bcrypt_hash(): void
    {
        $this->post('/register', [
            'name' => 'Casey', 'email' => 'casey@example.edu',
            'password' => 'correct-horse-9', 'password_confirmation' => 'correct-horse-9',
        ]);

        // Read the column itself, the way /leak does.
        $stored = DB::table('users')->where('email', 'casey@example.edu')->value('password');

        $this->assertNotNull($stored, 'Registering did not create the account at all.');
        $this->assertNotSame('correct-horse-9', $stored,
            "The password column holds exactly what was typed. Add 'password' => 'hashed' to casts() in app/Models/User.php.");
        $this->assertStringStartsWith('$2y$', $stored,
            "The password column does not hold a bcrypt hash (it should start with \$2y\$). Add 'password' => 'hashed' to casts() in app/Models/User.php.");
    }

    public function test_logging_in_checks_the_password_against_the_hash(): void
    {
        // The factory stores a bcrypt hash of 'password', as a hashing app would.
        User::factory()->create(['email' => 'hashed@example.edu']);

        $this->post('/login', ['email' => 'hashed@example.edu', 'password' => 'password']);

        $this->assertTrue($this->isAuthenticated(),
            "Logging in with the right password failed when the stored password is a hash: login() compares what was typed with the hash as plain text. Use Hash::check(\$credentials['password'], \$user->password).");
    }
}
