<?php

// Worked answer. Drills 3 and 4.
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

    // DRILL 3 fixed: the 'login' limit from AppServiceProvider, five a minute.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

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
    // DRILL 4 fixed: inside the group, so 'auth' runs first.
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->whereNumber('id')->name('todos.destroy');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
});
