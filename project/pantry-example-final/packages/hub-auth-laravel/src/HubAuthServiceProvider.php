<?php

namespace RpiPlatform\HubAuth;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class HubAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hub-auth.php', 'hub-auth');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([__DIR__.'/../config/hub-auth.php' => config_path('hub-auth.php')], 'hub-auth-config');
        $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'hub-auth-migrations');

        Socialite::extend('hub', fn ($app) => Socialite::buildProvider(HubProvider::class, [
            'client_id' => config('hub-auth.client_id'),
            'client_secret' => config('hub-auth.client_secret'),
            'redirect' => config('hub-auth.redirect'),
        ]));
    }
}
