<?php

// The routes -- supplied; you won't edit this file. The drills are in the controllers,
// the views, the config, the middleware and the lockfile.

use App\Http\Controllers\TodoController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

// The drills page, the schema/settings page, and each drill's Check result button.
Route::get('/', [WorkshopController::class, 'index']);
Route::get('/settings', [WorkshopController::class, 'settings']);
Route::get('/drills/{number}/check', [WorkshopController::class, 'check'])->whereNumber('number');

// The to-do app.
Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
Route::get('/todos/{todo}', [TodoController::class, 'show'])->whereNumber('todo')->name('todos.show');
Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');

// DRILL 3: a stand-in for "another website". It posts to /todos on its own. In the starter
// app the post succeeds, because /todos is excluded from the CSRF check (bootstrap/app.php)
// and the form has no token. Supplied; the fix is in the form and bootstrap/app.php.
Route::get('/attack', fn () => view('attack'));
