<?php

namespace Tests\Feature\Hw6;

use App\Http\Controllers\TodoController;
use App\Todos\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

// Part 1: the list is answered by a controller. Supplied -- leave the tests alone.
class Part1ControllerTest extends TestCase
{
    public function test_the_list_route_points_at_the_controller(): void
    {
        // Ask the router which code would answer GET /todos.
        $route = Route::getRoutes()->match(Request::create('/todos'));

        $this->assertSame(TodoController::class.'@index', $route->getActionName(),
            'GET /todos should be answered by TodoController\'s index() method.');
    }

    public function test_the_controller_still_shows_the_list(): void
    {
        $route = Route::getRoutes()->match(Request::create('/todos'));
        $this->assertSame(TodoController::class.'@index', $route->getActionName(),
            'GET /todos should be answered by TodoController\'s index() method.');

        $page = $this->get('/todos')->assertOk()->assertViewIs('todos.index');

        foreach (app(TodoList::class)->all() as $todo) {
            $page->assertSeeText($todo['title']);
        }
    }
}
