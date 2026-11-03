<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep a list of every SQL query a request runs, so the layout's footer can
        // count them. Supplied for the workshop; you would not leave this on in production.
        DB::enableQueryLog();

        // DRILL 7 fixed: the 'search' limit that throttle:search (routes/web.php) refers to.
        // Twenty searches a minute from one address; the twenty-first gets 429 Too Many Requests.
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
    }
}
