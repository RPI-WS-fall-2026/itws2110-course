<?php

namespace Tests\Feature\Drills;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Drill 7 (if you finish) -- the search box. Supplied; leave it alone.
class Drill7Test extends TestCase
{
    use RefreshDatabase;

    public function test_a_search_finds_only_your_own_todos(): void
    {
        $alex = User::factory()->create();
        $priya = User::factory()->create();
        Todo::factory()->for($alex)->create(['title' => 'Email the TA about my grade']);
        Todo::factory()->for($priya)->create(['title' => 'Email the landlord']);

        $html = $this->actingAs($priya)->get('/todos?q=Email')->assertOk()->getContent();

        $this->assertTrue(str_contains($html, 'Email the landlord'),
            "Priya searched for 'Email' and her own to-do was not in the results.");
        $this->assertFalse(str_contains($html, 'Email the TA about my grade'),
            "Priya searched for 'Email' and got Alex's to-do too. Start the search from \$request->user()->todos(), like the list.");
    }
}
