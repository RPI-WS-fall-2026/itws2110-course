# rpi-platform/hub-auth

Log a Laravel app in through the **RPI Project Platform hub** instead of keeping its own passwords. One Composer package, three routes, one migration.

```
browser ──GET /auth/redirect──▶ this app ──302──▶ hub /oauth/authorize ──(login if needed)──▶
        ◀──302 /auth/callback?code──┘ this app ──POST /oauth/token (code + PKCE verifier)──▶ hub
                                     this app ──GET /api/userinfo (bearer)──▶ hub
                                     this app mirrors the user, starts its own session, redirects to `return`
```

## Install (in this repository)

```json
"repositories": [{ "type": "path", "url": "../../packages/hub-auth-laravel" }],
"require": { "rpi-platform/hub-auth": "*" }
```

```bash
composer update rpi-platform/hub-auth
php artisan vendor:publish --tag=hub-auth-migrations
```

Set in `.env` (values come from the hub's admin page, *Issue client*):

```
HUB_URL=https://ta.biggermatrix.com         # as the browser sees the hub
HUB_INTERNAL_URL=http://hub-app:8000        # as this server sees it (docker compose); same as HUB_URL on Azure
HUB_CLIENT_ID=...
HUB_CLIENT_SECRET=...
```

Your `User` model needs `hub_id`, `name`, `email`, `role` columns (the migration adds `hub_id` and makes `password` nullable). Remove your `/api/register` route and your password login: the hub does that now.

In React, when `GET /api/user` answers 401, link to `/auth/redirect?return=<current path>`; for log out, link to `/auth/logout`.
