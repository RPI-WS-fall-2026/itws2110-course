<?php

namespace Tests\Feature\Hw6;

use App\Todos\TodoList;
use Tests\TestCase;

// Part 3: the filter, read from the query string. Supplied -- leave the tests alone.
class Part3FilterTest extends TestCase
{
    private function todos(): array
    {
        return app(TodoList::class)->all();
    }

    private function titles(bool $done): array
    {
        return array_column(array_filter($this->todos(), fn ($t) => $t['done'] === $done), 'title');
    }

    /** The text of the filter link marked aria-current="page", or null. */
    private function currentFilter(string $html): ?string
    {
        preg_match_all('/<a\b[^>]*\baria-current="page"[^>]*>(.*?)<\/a>/s', $html, $found);
        $labels = array_values(array_intersect(array_map(fn ($t) => trim(strip_tags($t)), $found[1]), ['All', 'Active', 'Done']));

        return count($labels) === 1 ? $labels[0] : null;
    }

    public function test_active_shows_only_what_is_not_done(): void
    {
        $page = $this->get('/todos?status=active')->assertOk();

        foreach ($this->titles(false) as $title) {
            $page->assertSeeText($title);
        }
        foreach ($this->titles(true) as $title) {
            $page->assertDontSeeText($title);
        }
    }

    public function test_done_shows_only_what_is_done(): void
    {
        $page = $this->get('/todos?status=done')->assertOk();

        foreach ($this->titles(true) as $title) {
            $page->assertSeeText($title);
        }
        foreach ($this->titles(false) as $title) {
            $page->assertDontSeeText($title);
        }
    }

    public function test_no_status_or_an_unknown_one_shows_everything(): void
    {
        foreach (['/todos', '/todos?status=all', '/todos?status=banana'] as $url) {
            $page = $this->get($url)->assertOk();
            foreach ($this->todos() as $todo) {
                $page->assertSeeText($todo['title']);
            }
            $page->assertSeeText('Showing '.count($this->todos()).' of '.count($this->todos()));
        }
    }

    public function test_the_page_says_how_many_are_showing(): void
    {
        $all = count($this->todos());

        $this->get('/todos?status=active')->assertSeeText('Showing '.count($this->titles(false)).' of '.$all);
        $this->get('/todos?status=done')->assertSeeText('Showing '.count($this->titles(true)).' of '.$all);
    }

    public function test_there_are_three_filter_links(): void
    {
        $html = $this->get('/todos')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<nav\b[^>]*aria-label="Filter"[^>]*>.*?<\/nav>/s', $html,
            'The filter links go inside <nav aria-label="Filter"> ... </nav>.');
        preg_match('/<nav\b[^>]*aria-label="Filter"[^>]*>(.*?)<\/nav>/s', $html, $nav);

        foreach (['All' => '', 'Active' => 'status=active', 'Done' => 'status=done'] as $label => $query) {
            $this->assertMatchesRegularExpression('/<a\b[^>]*>\s*'.$label.'\s*<\/a>/', $nav[1], "No filter link reads $label.");
            if ($query !== '') {
                $this->assertMatchesRegularExpression('/<a\b[^>]*href="[^"]*\?'.$query.'"[^>]*>\s*'.$label.'\s*<\/a>/', $nav[1],
                    "The $label link should go to ?$query.");
            }
        }
    }

    public function test_the_current_filter_is_marked(): void
    {
        $this->assertSame('All', $this->currentFilter($this->get('/todos')->getContent()),
            'On /todos, the All link (and only it) should have aria-current="page".');
        $this->assertSame('Active', $this->currentFilter($this->get('/todos?status=active')->getContent()),
            'On /todos?status=active, the Active link (and only it) should have aria-current="page".');
        $this->assertSame('Done', $this->currentFilter($this->get('/todos?status=done')->getContent()),
            'On /todos?status=done, the Done link (and only it) should have aria-current="page".');
    }
}
