<?php

namespace Tests\Feature\Hw6;

use App\Todos\TodoList;
use Tests\TestCase;

// What the starter already does. These pass before you change anything, and must still
// pass when you're done. Supplied -- leave the tests alone.
//
// $this->get('/url') sends a pretend request to the app, inside the framework: no browser,
// no server, no network. Then each assert... line checks one thing about the response.
class StarterTest extends TestCase
{
    public function test_the_home_page_loads(): void
    {
        $this->get('/')->assertOk()->assertSeeText('To-do list');
    }

    public function test_the_list_shows_every_to_do(): void
    {
        $page = $this->get('/todos')->assertOk();

        foreach (app(TodoList::class)->all() as $todo) {
            $page->assertSeeText($todo['title']);
        }
    }
}
