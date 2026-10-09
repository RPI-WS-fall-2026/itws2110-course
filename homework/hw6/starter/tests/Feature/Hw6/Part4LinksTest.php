<?php

namespace Tests\Feature\Hw6;

use App\Todos\TodoList;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

// Part 4: named routes, and links written with route(). Supplied -- leave the tests alone.
//
// route() writes out the full address (http://localhost/todos), so a link with a URL typed
// in by hand -- href="/todos" -- does not match what these look for.
class Part4LinksTest extends TestCase
{
    private function todos(): array
    {
        return app(TodoList::class)->all();
    }

    public function test_both_routes_have_names(): void
    {
        $this->assertTrue(Route::has('todos.index'), 'No route is named todos.index yet.');
        $this->assertTrue(Route::has('todos.show'), 'No route is named todos.show yet.');

        $id = $this->todos()[0]['id'];
        $this->assertSame(url('/todos'), route('todos.index'));
        $this->assertSame(url('/todos/'.$id), route('todos.show', ['id' => $id]));
    }

    public function test_each_title_in_the_list_links_to_its_page(): void
    {
        $this->assertTrue(Route::has('todos.show'), 'No route is named todos.show yet.');
        $html = $this->get('/todos')->assertOk()->getContent();

        foreach ($this->todos() as $todo) {
            $href = preg_quote(route('todos.show', ['id' => $todo['id']]), '/');
            $this->assertMatchesRegularExpression(
                '/<a\b[^>]*href="'.$href.'"[^>]*>\s*'.preg_quote(e($todo['title']), '/').'\s*<\/a>/', $html,
                "\"{$todo['title']}\" should be a link to route('todos.show', ...) for that to-do.");
        }
    }

    public function test_the_page_for_one_to_do_links_back_to_the_list(): void
    {
        $this->assertTrue(Route::has('todos.index'), 'No route is named todos.index yet.');
        $html = $this->get('/todos/'.$this->todos()[0]['id'])->assertOk()->getContent();

        $href = preg_quote(route('todos.index'), '/');
        $this->assertMatchesRegularExpression('/<a\b[^>]*href="'.$href.'"[^>]*>\s*Back to the list\s*<\/a>/', $html,
            'The page should have a link that reads "Back to the list" and goes to route(\'todos.index\').');
    }

    public function test_the_nav_bar_links_to_the_list_by_name(): void
    {
        $this->assertTrue(Route::has('todos.index'), 'No route is named todos.index yet.');
        $html = $this->get('/')->assertOk()->getContent();

        $href = preg_quote(route('todos.index'), '/');
        $this->assertMatchesRegularExpression('/<nav class="site-nav".*?<a\b[^>]*href="'.$href.'"[^>]*>\s*To-dos\s*<\/a>.*?<\/nav>/s', $html,
            'In the layout, the To-dos link should use route(\'todos.index\').');
    }
}
