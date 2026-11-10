<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Week 11, drill 7: twenty searches a minute from one address.
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // DRILL 3: name a limit for the login form here -- five tries a minute for one email
        // from one address -- then put throttle:login on the POST /login route:
        //
        // RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
        //     ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
