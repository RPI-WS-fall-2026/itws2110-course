<?php

// The API's routing table. Everything here starts with /api (bootstrap/app.php).

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Anyone.
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{item}', [ItemController::class, 'show'])->whereNumber('item');

// Log in and register, rate-limited (app/Providers/AppServiceProvider.php): security, 11/3.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

// Logged in. auth:sanctum answers 401 for anyone who is not.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{item}', [ItemController::class, 'update'])->whereNumber('item');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->whereNumber('item');
    Route::post('/items/{item}/use', [ItemController::class, 'use'])->whereNumber('item');

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [UserController::class, 'index']);
});
