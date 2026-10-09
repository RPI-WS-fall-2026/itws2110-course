<?php

// Homework 6 -- the routing table. Every page of the app starts as a line in this file.
// The README says what each part asks for; the TODOs below say where it goes.

use App\Todos\TodoList;
use Illuminate\Support\Facades\Route;

// The home page -- supplied, and finished.
Route::get('/', function () {
    return view('home');
});

// The list of to-dos -- supplied, and working. Open http://localhost:8010/todos.
// Laravel sees that this function asks for a TodoList and hands it one.
//
// TODO Part 1: move this function's body into TodoController's index() method, and
//              replace the whole route with one line that points at it.
// TODO Part 4: give the route the name todos.index.
Route::get('/todos', function (TodoList $todos) {
    return view('todos.index', ['todos' => $todos->all()]);
});

// TODO Part 2: a route for one to-do -- GET /todos/{id} -> TodoController's show() method,
//              named todos.show.
