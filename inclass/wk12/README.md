# Week 12 — the login lab (Fri 11/13)

[← Week 12](../../weeks/week-12.md)

No starter app this week. The lab is your own team project: you add the login to the `api/`
you built for Homework 7, in class, with the pantry example open beside you and a check
script that sends the requests from Tuesday's Part 1 to your API. Everything you finish is
the start of [Homework 9](../../homework/hw9/README.md), which is assigned today.

**Bring your `api/` running** (Homework 8, due tonight, hardened it; the login goes on top):
`docker compose watch` up, your app opening at http://localhost:8010 (use your own web port if
it is not 8010), and this showing your five routes:

```bash
docker compose exec app php artisan route:list --path=api
```

**Finished, or waiting on a teammate?** The [optional login drills](laravel/README.md) break
and fix each idea from Tuesday on the week 11 to-do app, with a Check result button for each,
and they are the warm-up for the optional [Homework 10](../../homework/hw10_optional_individual/README.md).

## What to do

Work through the nine files in this order. Every one of them exists in
[`project/pantry-example-final`](../../project/pantry-example-final/) under the same path;
read the example's version, then write yours. Split the work: one teammate on steps 1–5 (the
API), one on steps 6–9 (the pages), and swap who types at each pause.

| # | File | What to do | Minutes |
|---|---|---|---|
| 1 | `api/app/Models/User.php` | Add `'password' => 'hashed'` to `casts()`. Make sure `role` is **not** in your `#[Fillable]` list. | 5 |
| 2 | `api/app/Http/Controllers/Api/AuthController.php` | New file: `register()`, `login()`, `logout()`, `user()`. Copy the example's whole; its messages are what the check looks for. | 15 |
| 3 | `api/routes/api.php` | Add `POST /register`, `POST /login` (both with their `throttle:` middleware), and inside an `auth:sanctum` group: `GET /user`, `POST /logout`, and your three write routes, which still answer 501 for now. | 10 |
| | **Pause 1** (about 30 minutes into class) | Everyone: `route:list --path=api` shows `login`, `register`, `user`, `logout`, and your three writes with `auth:sanctum` beside them. The check cannot pass yet: the session arrives in step 5. | |
| 4 | `api/app/Providers/AppServiceProvider.php` | Add the two `RateLimiter::for()` calls (`login`, `register`) in `boot()`. | 5 |
| 5 | `api/bootstrap/app.php` and `docker-compose.yml` | Add `$middleware->statefulApi();` to `withMiddleware`. In `docker-compose.yml`, the `app` service's `SANCTUM_STATEFUL_DOMAINS` must name **your** web port: `localhost:8010,web:5173`. The pantry's copy says `5181`. | 5 |
| | **Pause 2** (about 45 minutes into class) | Everyone runs the check (below). 9 of 9 is the goal for the API. Stuck? Read the FAIL line, then the Troubleshooting table, then ask. A team at 9 helps the team next to it. | |
| 6 | `web/src/api.js` | Add `currentUser`, `login`, `register`, `logout` from the example. The `request()` helper you already have does the CSRF dance. | 5 |
| 7 | `web/src/session.jsx` | New file: copy the example's `SessionProvider` and `useSession()`. Wrap your app in `<SessionProvider>` in `main.jsx`. | 10 |
| 8 | `web/src/pages/AuthPages.jsx` | New file: the Log in and Register forms, copied from the example; add the two routes in `App.jsx`. | 15 |
| 9 | Your layout | Show the name from `useSession()` when someone is logged in, with a Log out button; a Log in link otherwise. Your midterm's `rules.js` stays — it only hides buttons now. | 10 |

Steps 1 to 5 are the API; the check script covers them. Steps 6 to 9 are the pages, and they
are what you keep working on in Homework 9.

## The check

From your repo's root, with `docker compose watch` running:

```bash
bash ../itws2110-course/inclass/wk12/check-login.sh http://localhost:8010
```

(Adjust the two paths: the first is wherever your copy of the course repo is, the second is
your web port.) It needs only `curl`. It makes a throw-away account, logs in and out, and
sends the three requests from Tuesday's "the attacker does not use your page" slide:

```
Checking http://localhost:8010 (resource: items)
PASS  1. POST /api/register -> 201, and the extra "role":"admin" in the body was ignored
PASS  2. POST /api/login with the wrong password -> 422
PASS  3. the wrong-password and unknown-email messages are identical
PASS  4. POST /api/login -> 200 and a session cookie
PASS  5. GET /api/user with the cookie -> 200, with the email
PASS  6. GET /api/user without a cookie -> 401
PASS  7. POST /api/items without a cookie -> 401 (not 501)
PASS  8. POST /api/logout -> 204, and GET /api/user is 401 again
PASS  9. twenty-one bad logins -> 429 (throttle:login)
9 passed, 0 failed
```

Your resource is probably not `items`. Pass its path as the second argument:

```bash
bash ../itws2110-course/inclass/wk12/check-login.sh http://localhost:8010 reviews
```

A FAIL prints the status code and the start of the reply it got (Laravel's `"message"`, when
there is one), which is usually enough to say which step is missing. Steps 1 to 5 above are in
the order the checks need them.

## Hand in

At the end of class, commit and push what you have to your team repository — the API side of
Homework 9, finished or not. Everyone commits something. The check does not have to pass
today; it has to pass by Fri 11/20.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| Checks 1 and 4 get 500 `"Session store not set on request."` | Sanctum did not treat the request as coming from your web app. Pass the check **exactly** the host and port in `SANCTUM_STATEFUL_DOMAINS` (`localhost`, not `127.0.0.1`), and make sure `statefulApi()` is in `bootstrap/app.php`. Restart `docker compose watch` after editing the compose file. |
| Check 4 gets 200 but check 5 gets 401 | Sanctum did not treat the request as stateful. `SANCTUM_STATEFUL_DOMAINS` in `docker-compose.yml` must include the host and port you passed to the script (`localhost:8010`), and `statefulApi()` must be in `bootstrap/app.php`. Restart `docker compose watch` after changing the compose file. |
| Check 1 gets 419 | Same cause: the CSRF cookie came from an address Sanctum does not consider yours. |
| Check 1 gets 500 with another message | Read the message first. Usually `password` is not in `#[Fillable]`, or there is no `role` default on the `users` table (`->default('user')` in the migration). Read `docker compose logs app`. |
| Check 3 fails | Your `login()` returns different messages for a wrong password and an unknown email. Use one message for both, so a stranger cannot learn which emails have accounts (Tuesday's login slide). |
| Check 7 gets 501 | Your write routes are outside the `auth:sanctum` group. Move them inside; they may keep answering 501 for now. |
| Check 9 gets 422 every time | The `throttle:login` middleware is not on the route, or `RateLimiter::for('login', …)` is not in `AppServiceProvider::boot()`. |
| The pages: logging in works, then a refresh logs you out | `main.jsx` does not wrap the app in `<SessionProvider>`, or `currentUser()` is not called on load. |
