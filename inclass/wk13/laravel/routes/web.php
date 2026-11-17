<?php

// Supplied -- leave it alone. Who may reach each route is decided here, before any
// controller runs (weeks 11 and 12).

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\Workshop\WorkshopController;
use Illuminate\Support\Facades\Route;

// The drills page.
Route::get('/', [WorkshopController::class, 'index']);

// Two pages for the drills: one that always fails (drill 1), and one that prints what the
// app believes about the request it just got (drill 4).
Route::get('/boom', [WorkshopController::class, 'boom']);
Route::get('/request', [WorkshopController::class, 'request']);

// Logging in. 'guest' sends someone who is already logged in on to their list.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Everything in this group needs a login.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/todos', [TodoController::class, 'index'])
        ->middleware('throttle:search')
        ->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::get('/todos/{id}', [TodoController::class, 'show'])->whereNumber('id')->name('todos.show');
    Route::patch('/todos/{id}/toggle', [TodoController::class, 'toggle'])->whereNumber('id')->name('todos.toggle');
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->whereNumber('id')->name('todos.destroy');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
});
