<?php

namespace RpiPlatform\HubAuth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

/**
 * Socialite provider for the hub. The same shape as Socialite's GitHub or Google providers,
 * because the hub speaks the same protocol they do: OAuth 2.0 authorization code, with PKCE
 * (RFC 7636) so a stolen code is useless without the verifier this server kept.
 *
 * One difference from a stock provider: the `state`, the PKCE verifier and the return path
 * travel in ONE short-lived, encrypted, HttpOnly cookie instead of the session. Laravel
 * saves the whole session at the end of every request, so a slow GET /api/user that the
 * React page fired a moment before "Log in" was clicked could save a stale copy and erase
 * the state -- an intermittent "invalid state" error that only shows up under load or in
 * fast tests. A cookie of its own has no such race. (EncryptCookies, in the `web` group,
 * encrypts it on the way out and verifies it on the way back, so it cannot be forged.)
 */
class HubProvider extends AbstractProvider implements ProviderInterface
{
    public const COOKIE = 'hub_auth_flow';

    protected $scopes = ['openid', 'profile', 'email', 'roles'];

    protected $scopeSeparator = ' ';

    protected $usesPKCE = true;

    /** The in-flight values while building the redirect. */
    private array $flow = [];

    /** Where to send the person after the callback (a path on this app). */
    public function returnTo(?string $path): static
    {
        $this->flow['return'] = $path;

        return $this;
    }

    public function redirect(): RedirectResponse
    {
        $this->flow['state'] = $this->getState();
        $this->flow['verifier'] = $this->getCodeVerifier();
        // 10 minutes is plenty to log in on the hub; then the cookie is gone.
        Cookie::queue(Cookie::make(self::COOKIE, json_encode($this->flow), minutes: 10, path: '/', httpOnly: true, sameSite: 'lax'));

        return new RedirectResponse($this->getAuthUrl($this->flow['state']));
    }

    /** What the callback request brought back in the cookie. */
    public function flow(): array
    {
        $raw = $this->request->cookie(self::COOKIE);
        $data = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($data) ? $data : [];
    }

    /** Forget the flow cookie once the round trip is over. */
    public function forgetFlow(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE, '/'));
    }

    protected function getCodeChallenge()
    {
        $hashed = hash('sha256', $this->flow['verifier'], true);

        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    protected function hasInvalidState()
    {
        $state = $this->flow()['state'] ?? '';

        return $state === '' || ! hash_equals($state, (string) $this->request->input('state'));
    }

    protected function getTokenFields($code)
    {
        return array_merge([
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
            'code_verifier' => $this->flow()['verifier'] ?? '',
        ], $this->parameters);
    }

    /** Browser-facing: the hub's login/authorize page. */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(config('hub-auth.hub_url').'/oauth/authorize', $state);
    }

    /** Server-to-server: swap the code for tokens. */
    protected function getTokenUrl()
    {
        return config('hub-auth.hub_internal_url').'/oauth/token';
    }

    /** Server-to-server: who is this token for, and what are they in this project? */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(config('hub-auth.hub_internal_url').'/api/userinfo', [
            'headers' => ['Authorization' => 'Bearer '.$token, 'Accept' => 'application/json'],
        ]);

        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'nickname' => $user['preferred_username'] ?? null,
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }
}
