<?php

namespace RpiPlatform\HubAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as HubUser;

/**
 * Turn the hub's answer into a local user row. Called on every login, so a name change or
 * a new role on the hub reaches this app the next time the person logs in.
 *
 * Matching order: by `hub_id` (the normal case); else by email, which links an account
 * that existed before the app joined the platform (or one a seeder made) instead of
 * making a duplicate; else a new row.
 *
 * An app that needs more (say, a `rcs_id` column) binds its own class to this one in its
 * AppServiceProvider: `$this->app->bind(HubUserSync::class, MyHubUserSync::class)`.
 */
class HubUserSync
{
    public function __invoke(HubUser $hubUser): Authenticatable
    {
        $model = config('hub-auth.user_model');
        $column = config('hub-auth.hub_id_column');
        $claims = $hubUser->getRaw();

        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = $model::query()->where($column, $hubUser->getId())->first()
            ?? $model::query()->where('email', $hubUser->getEmail())->first()
            ?? new $model;

        // forceFill: these columns are deliberately not fillable from requests in the app,
        // because the hub is the only source of truth for them.
        $user->forceFill([
            $column => $hubUser->getId(),
            'name' => $hubUser->getName(),
            'email' => $hubUser->getEmail(),
            'role' => $claims['role'] ?? 'user',
        ])->save();

        return $user;
    }
}
