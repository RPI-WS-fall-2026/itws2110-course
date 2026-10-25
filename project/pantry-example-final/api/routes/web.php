<?php

// No Blade pages: the React app (web/) is the front end. This only answers the root,
// so opening the API's own address says where to go.

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['message' => 'Floor Pantry API. The app is at http://localhost:5181.']));
