<?php

// The routes -- supplied. The drills are in the controllers, the views, the config, the
// middleware and the lockfile. Two drills edit this file: drill 6 (one route, then undone)
// and drill 7 (the throttle on the search).

use App\Http\Controllers\TodoController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

// The drills page, the schema/settings page, and each drill's Check result button.
Route::get('/', [WorkshopController::class, 'index']);
Route::get('/settings', [WorkshopController::class, 'settings']);
Route::get('/drills/{number}/check', [WorkshopController::class, 'check'])->whereNumber('number');

// The to-do app.
// DRILL 6, step 3: put your new middleware on this one route with ->middleware(...), look at
// the headers, then take it off again in step 4 and register it for every route instead.
// DRILL 7: ->middleware('throttle:search') on this route, with the 'search' limit named in
// app/Providers/AppServiceProvider.php.
Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
Route::get('/todos/{todo}', [TodoController::class, 'show'])->whereNumber('todo')->name('todos.show');
Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');

// DRILL 3: a stand-in for "another website". It posts to /todos on its own. In the starter
// app the post succeeds, because /todos is excluded from the CSRF check (bootstrap/app.php)
// and the form has no token. Supplied; the fix is in the form and bootstrap/app.php.
Route::get('/attack', fn () => view('attack'));
