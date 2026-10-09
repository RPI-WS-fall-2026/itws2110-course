<?php

namespace Tests\Feature\Hw6;

use App\Http\Controllers\TodoController;
use App\Todos\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

// Part 2: a page for one to-do. Supplied -- leave the tests alone.
// The to-dos come from TodoList, so these keep working if the list changes.
class Part2DetailTest extends TestCase
{
    private function todos(): array
    {
        return app(TodoList::class)->all();
    }

    private function unknownId(): int
    {
        return max(array_column($this->todos(), 'id')) + 1000;
    }

    public function test_each_to_do_has_a_page_answered_by_the_controller(): void
    {
        foreach ($this->todos() as $todo) {
            $url = '/todos/'.$todo['id'];
            try {
                $action = Route::getRoutes()->match(Request::create($url))->getActionName();
            } catch (NotFoundHttpException) {
                $action = 'no route at all';
            }
            $this->assertSame(TodoController::class.'@show', $action,
                "GET $url should be answered by TodoController's show() method.");

            $this->get($url)->assertOk()->assertViewIs('todos.show');
        }
    }

    public function test_the_page_shows_the_title_status_and_due_date(): void
    {
        foreach ($this->todos() as $todo) {
            $this->get('/todos/'.$todo['id'])
                ->assertOk()
                ->assertSeeText($todo['title'])
                ->assertSeeText('Status: '.($todo['done'] ? 'done' : 'active'))
                ->assertDontSeeText('Status: '.($todo['done'] ? 'active' : 'done'))
                ->assertSeeText('Due: '.$todo['due']);
        }
    }

    public function test_the_page_shows_the_notes_or_says_there_are_none(): void
    {
        foreach ($this->todos() as $todo) {
            $page = $this->get('/todos/'.$todo['id'])->assertOk();

            if ($todo['notes'] === '') {
                $page->assertSeeText('No notes.');
            } else {
                // Look for the words before any tag in the notes; test_notes_are_escaped
                // checks how the tag itself arrives.
                $page->assertSeeText(trim(strtok($todo['notes'], '<')))->assertDontSeeText('No notes.');
            }
        }
    }

    public function test_notes_are_escaped(): void
    {
        $checked = 0;
        foreach ($this->todos() as $todo) {
            if ($todo['notes'] !== strip_tags($todo['notes'])) {
                // The notes contain a tag. It must arrive as text (&lt;b&gt;), not as a real tag.
                $this->get('/todos/'.$todo['id'])
                    ->assertOk()
                    ->assertSee($todo['notes'])              // escaped: this is what {{ }} sends
                    ->assertDontSee($todo['notes'], false);  // raw: this is what {!! !!} sends
                $checked++;
            }
        }
        $this->assertGreaterThan(0, $checked, 'TodoList should hold a to-do whose notes contain a tag.');
    }

    public function test_the_page_uses_the_layout(): void
    {
        $todo = $this->todos()[0];

        $this->get('/todos/'.$todo['id'])
            ->assertOk()
            ->assertSee('<nav class="site-nav"', false)
            ->assertSee('<title>'.e($todo['title']), false);   // <x-layout :title="...">
    }

    public function test_an_unknown_id_is_a_404(): void
    {
        $this->get('/todos/'.$this->todos()[0]['id'])->assertOk();   // the page exists first
        $this->get('/todos/'.$this->unknownId())->assertNotFound();
    }

    public function test_an_id_that_is_not_a_number_is_a_404(): void
    {
        $this->get('/todos/'.$this->todos()[0]['id'])->assertOk();   // the page exists first
        $this->get('/todos/abc')->assertNotFound();
    }
}
