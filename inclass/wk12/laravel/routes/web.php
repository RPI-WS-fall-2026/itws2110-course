<?php

// The routes. Two drills edit this file:
//   DRILL 3: a limit on POST /login        (and the limit's definition, in AppServiceProvider)
//   DRILL 4: one route is outside the login
// Read it top to bottom: who may reach each route is decided here, before any controller runs.

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

// The drills page, the leaked backup (drill 1) and each drill's Check result button. Supplied.
Route::get('/', [WorkshopController::class, 'index']);
Route::get('/leak', [WorkshopController::class, 'leak']);
Route::get('/drills/{number}/check', [WorkshopController::class, 'check'])->whereNumber('number');

// Logging in. 'guest' sends someone who is already logged in on to their list.
// The name 'login' matters: it is where the 'auth' middleware sends a visitor who is not logged in.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // DRILL 3: nothing limits this route, so a script can guess passwords as fast as the
    // server answers. Name a limit called 'login' in AppServiceProvider, then add
    //     ->middleware('throttle:login')
    // to this route.
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Everything in this group needs a login. 'auth' runs before the controller: a visitor who is
// not logged in never reaches it, and is sent to the 'login' route instead.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/todos', [TodoController::class, 'index'])
        ->middleware('throttle:search')   // week 11, drill 7
        ->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::get('/todos/{id}', [TodoController::class, 'show'])->whereNumber('id')->name('todos.show');
    Route::patch('/todos/{id}/toggle', [TodoController::class, 'toggle'])->whereNumber('id')->name('todos.toggle');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
});

// DRILL 4: this route was added after the group above, and outside it. The page only shows a
// Delete button to someone logged in, but the route does not care who is asking. Move this
// line inside the auth group.
Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->whereNumber('id')->name('todos.destroy');
