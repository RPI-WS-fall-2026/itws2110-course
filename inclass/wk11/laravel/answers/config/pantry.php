<?php

// DRILL 4 fixed: the key is read from the environment, not written here. The name lives in
// .env.example (committed, with a placeholder); the value lives in .env (never committed).
return [
    'api_key' => env('PANTRY_API_KEY'),
];
