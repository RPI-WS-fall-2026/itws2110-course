<?php

// No Blade pages: the React app (web/) is the front end.
//
// In development Vite serves the pages, and only /api and /sanctum reach Laravel.
// In production (the root Dockerfile) the React build sits in public/, in the same container:
// the server sends the built files itself, and every other path lands here. Sending
// index.html back lets React Router show the page, so a deep link like /items/3 or a
// refresh on it works. Deployment, 11/17.

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::fallback(function (Request $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => 'Not found.'], 404);
    }
    $index = public_path('index.html');
    if (is_file($index)) {
        return response()->file($index, ['Cache-Control' => 'no-store']);
    }

    return response()->json(['message' => 'Floor Pantry API. In development the app is at http://localhost:5181.']);
});
