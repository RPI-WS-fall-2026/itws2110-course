<?php

// The three browser routes every project app gets. They use the `web` middleware group,
// so the app's own session cookie and CSRF protection apply.

use Illuminate\Support\Facades\Route;
use RpiPlatform\HubAuth\Http\Controllers\HubAuthController;

Route::middleware('web')->group(function () {
    // GET /auth/redirect?return=/items/5  -- start the trip to the hub.
    Route::get('/auth/redirect', [HubAuthController::class, 'redirect'])->name('hub-auth.redirect');
    // GET /auth/callback?code=...&state=...  -- the hub sends the browser back here.
    Route::get('/auth/callback', [HubAuthController::class, 'callback'])->name('hub-auth.callback');
    // GET /auth/logout  -- end this app's session, then (by default) the platform's.
    Route::get('/auth/logout', [HubAuthController::class, 'logout'])->name('hub-auth.logout');
});
