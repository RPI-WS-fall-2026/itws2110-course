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
        // Brute-force protection for log in: 20 tries a minute for one email from one
        // address, then 429 Too Many Requests. Keyed on the email as well as the address,
        // as Breeze does, so one person's typos never lock out a whole dorm network.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(20)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // New accounts: 60 a minute from one address.
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
