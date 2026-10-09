<?php

namespace App\Http\Controllers;

use App\Todos\TodoList;

// Homework 6 -- the controller for the to-do pages. Each public method answers one route.
// You never call these methods yourself: the router does, when a request matches.
class TodoController extends Controller
{
    // TODO Part 1: the list. Move the body of the /todos route in routes/web.php here.
    //              Ask for the to-dos the same way the route did: index(TodoList $todos)
    // TODO Part 3: the filter. Also ask for the request -- index(Request $request, TodoList $todos)
    //              -- and read ?status= from it.
    public function index()
    {
        //
    }

    // TODO Part 2: one to-do. show(TodoList $todos, int $id)
    //              Unknown id -> a real 404.
}
