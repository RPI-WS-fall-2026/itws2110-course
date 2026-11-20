<?php

namespace RpiPlatform\HubAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use RpiPlatform\HubAuth\HubProvider;
use RpiPlatform\HubAuth\HubUserSync;

class HubAuthController extends Controller
{
    /**
     * GET /auth/redirect?return=/items/5
     * Remember where the person was (a path on this app only -- never a full URL, so this
     * cannot be used as an open redirect), then send them to the hub.
     */
    public function redirect(Request $request)
    {
        $return = (string) $request->query('return', '');
        $return = (str_starts_with($return, '/') && ! str_starts_with($return, '//')) ? $return : null;

        /** @var HubProvider $hub */
        $hub = Socialite::driver('hub');

        return $hub->scopes(config('hub-auth.scopes'))->returnTo($return)->redirect();
    }

    /**
     * GET /auth/callback?code=...&state=...
     * The provider checks `state` (CSRF on the login itself), exchanges the code for a
     * token with the PKCE verifier, and fetches the claims. Then this app logs the mirrored
     * user in with its OWN session cookie -- from here on the hub is not involved until the
     * next login.
     */
    public function callback(Request $request, HubUserSync $sync)
    {
        /** @var HubProvider $hub */
        $hub = Socialite::driver('hub');
        $hubUser = $hub->user();
        $return = $hub->flow()['return'] ?? null;
        $hub->forgetFlow();

        $user = $sync($hubUser);

        // No "remember me" cookie: the session is enough, and Laravel names that cookie the same
        // in every app, so on a laptop two apps on localhost would overwrite each other's.
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect($return ?? config('hub-auth.home'));
    }

    /**
     * GET /auth/logout
     * End this app's session. With single logout on, continue to the hub so the platform
     * session ends too, and the hub sends the browser back here (to an address it knows).
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $home = url(config('hub-auth.home'));
        if (! config('hub-auth.single_logout')) {
            return redirect($home);
        }

        return redirect()->away(config('hub-auth.hub_url').'/auth/logout?'.http_build_query(['redirect' => $home]));
    }
}
