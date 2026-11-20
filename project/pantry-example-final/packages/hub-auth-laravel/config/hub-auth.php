<?php

// How this app talks to the hub. Every value is an environment variable so the same image
// runs on a laptop (docker compose) and on Azure.
return [
    // The hub as the BROWSER reaches it: where we send people to log in.
    'hub_url' => rtrim((string) env('HUB_URL', 'http://localhost:5181'), '/'),

    // The hub as THIS SERVER reaches it, for the code-for-token exchange and /api/userinfo.
    // Inside docker compose that is the service name; on Azure it is the public URL again.
    'hub_internal_url' => rtrim((string) env('HUB_INTERNAL_URL', env('HUB_URL', 'http://localhost:5181')), '/'),

    // Issued by a platform admin on the hub (Admin -> Projects -> Issue client).
    'client_id' => env('HUB_CLIENT_ID'),
    'client_secret' => env('HUB_CLIENT_SECRET'),

    // Must match one of the redirect URIs registered with the client, exactly.
    'redirect' => env('HUB_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/auth/callback'),

    'scopes' => ['openid', 'profile', 'email', 'roles'],

    // The Eloquent user model to mirror hub users into, and the column that holds the hub's id.
    'user_model' => env('HUB_USER_MODEL', 'App\\Models\\User'),
    'hub_id_column' => 'hub_id',

    // Where to send the browser after log in when no `return` path was given, and after log out.
    'home' => '/',

    // Also end the platform session on log out (single logout), or only this app's?
    'single_logout' => (bool) env('HUB_SINGLE_LOGOUT', true),
];
