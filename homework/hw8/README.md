# Homework 8 — Harden your API

**Due Friday 11/13, 11:59 PM. Team homework, in your team repo. Everyone commits.**

Friday's lab had you find six holes in a to-do app and close each one, and a seventh drill
limited how fast anyone can call a route. This homework is the same defenses on your own project: the `api/` your team built for Homework 7 and the React
app in `web/`. Some your project already has, because the framework's defaults are the
security; some it does not, because nobody added them. The example below uses a resource
called `Review` at `/api/reviews`, as Homework 7 did. Use your own name.

## What to do, and what is graded

1. **Security headers** — a `SecurityHeaders` middleware that sets four headers on every
   response, appended in `bootstrap/app.php`.
2. **CSRF and the cookie** — `statefulApi()` is on, no route is excluded from the CSRF check,
   and the session cookie is `HttpOnly` and `SameSite`.
3. **Injection** — every query binds its values; a `sort` or search parameter is validated
   against a list.
4. **XSS** — no `dangerouslySetInnerHTML` in `web/src`, no `{!! !!}` in Blade.
5. **Secrets** — `.env` is git-ignored, `env()` is only called in `config/`, `.env.example`
   holds names not values, and no response includes a password hash.
6. **`composer audit` reports nothing.**
7. **Rate limiting** — a named limit applied to every `/api` route, so the 21st request in a
   minute from one address gets `429`.
8. **`PROCESS.md`** — this week's entry says which of the seven your project already had, which
   you added, and what you changed.

The grader clones your repo, runs `docker compose watch`, requests one endpoint with
`curl -i` (and a short loop of 25 requests), reads the places named above, runs `composer audit`,
and reads `PROCESS.md`.
Each of the eight items is a line in the rubric.

## Two kinds of commands

As in Homework 7, nothing is installed on your laptop. Generators run in a throw-away
container that shares your folder; anything that needs your app runs inside the containers
`docker compose watch` started.

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan make:middleware SecurityHeaders
```

```bash
docker compose exec app composer audit
```

## 1. Security headers — drill 6

The one defense no Laravel app has by default: a middleware that runs on every response and
sets four headers, and one line that registers it. Drill 6 did this in four steps (generate,
set the headers, try it on one route, then append it for every route); here, go straight to
every route. The Laravel [*Middleware*](https://laravel.com/framework/docs/13.x/middleware)
page is the reference.

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan make:middleware SecurityHeaders
```

`api/app/Http/Middleware/SecurityHeaders.php`:

```php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('Content-Security-Policy', "default-src 'self'");
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

    return $response;
}
```

`api/bootstrap/app.php`, inside `withMiddleware`. Without this line the class exists and
never runs:

```php
$middleware->append(\App\Http\Middleware\SecurityHeaders::class);
```

Check it. All four headers should be in the answer, and also on a URL that does not exist:

```bash
curl -i http://localhost:8010/api/reviews | head -20
```

## 2. CSRF and the cookie — drill 3

Three settings, two of which Laravel already has right.

- `api/bootstrap/app.php` calls `$middleware->statefulApi();` inside `withMiddleware`.
  Homework 9's login depends on it.
- No `validateCsrfTokens(except: [...])` with a route in it. If a teammate added one to make
  a POST work, delete it; Homework 9 sends the token properly from `api.js`.
- `config/session.php`: `http_only` is `true` and `same_site` is `'lax'`. These are the
  defaults; leave them.

## 3. Injection — drill 1

Read every controller method that touches the database. Eloquent's `where()`, `orderBy()`
and `find()` bind their values. What to look for is raw SQL with a variable pasted in, and a
sort parameter passed straight to `orderBy()`, which lets a stranger name any column.
Validate it against a list:

```php
$sort = $request->validate(['sort' => ['sometimes', 'in:created_at,rating,title']])['sort'] ?? 'created_at';
```

Raw SQL is fine when it binds: `DB::select('select ... where title like ?', ["%{$q}%"])`.
Not fine: `DB::select("... like '%$q%'")`.

Check it: a quote in the query string must be treated as text, not answered with a 500.

```bash
curl -i "http://localhost:8010/api/reviews?q='%20OR%201=1%20--" | head -1
```

## 4. XSS — drill 2

React escapes `{value}`, as Blade's `{{ }}` does. The one way to turn that off is
`dangerouslySetInnerHTML`; if a page uses it for a description with formatting, render the
pieces as elements instead. In `api/resources/views`, if you have any, `{!! $x !!}` is the
same switch.

```bash
grep -rn dangerouslySetInnerHTML web/src; grep -rn '{!!' api/resources
```

Then the lab's test: put `<script>alert(1)</script>` into one row through the seeder, open
the page, and see text, not a dialog.

## 5. Secrets — drill 4

Four things, three of which a fresh Laravel app already has.

- `api/.gitignore` lists `.env`.
- `env()` is called in `config/` files and nowhere else; code reads `config('services.x.key')`.
- `api/.env.example` records names, not values: anything ending in `_KEY` or containing
  `SECRET`, `TOKEN` or `PASSWORD` is empty there (`DB_PASSWORD` for the local MySQL container
  is the exception). If a real key was ever committed, it has to be **rotated**, not just
  deleted; `git log -p` keeps it.
- No response carries a password hash: the `User` model's `#[Hidden]` list includes
  `password` and `remember_token`.

```bash
grep -rn 'env(' api/app api/routes
```

## 6. Dependencies — drill 5

```bash
docker compose exec app composer audit
```

If it names a package, update past the advisory from your laptop, so the lockfile in your
repo changes, then rebuild:

```bash
docker run --rm -v "$PWD/api":/app -w /app composer:2 composer update vendor/package --no-install --no-scripts
```

```bash
docker compose build app
```

Do the same for the front end:

```bash
docker run --rm -v "$PWD/web":/app -w /app node:22-bookworm-slim npm audit
```

## 7. Rate limiting — drill 7

Nothing so far stops a script from calling your API as fast as it can, whether that is a
scraper, someone guessing values, or a flood of requests that keeps real users out. A rate limit
is a middleware that counts requests per address and answers `429 Too Many Requests` once the
count is over, before your controller runs. You do not write it: Laravel's `throttle` middleware
does the counting, and the colon passes it the name of a limit that you define once.

One honest caveat: this protects your app from a single noisy address. A real distributed
attack (DDoS) comes from thousands of addresses and is stopped upstream, by a CDN or firewall in
front of the server. The limit is still the layer your code owns, and it is the same mechanism
Homework 9 puts on the login route.

`api/app/Providers/AppServiceProvider.php`, inside `boot()` (add the three `use` lines for
`Limit`, `Request` and `RateLimiter` at the top):

```php
RateLimiter::for('api', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
```

`api/bootstrap/app.php`, inside `withMiddleware`, applies the limit named `api` to every route
in `routes/api.php`:

```php
$middleware->throttleApi();
```

Check it. The first 20 answers are `200`, then `429`:

**Mac or Linux (Terminal):**

```bash
for i in $(seq 1 25); do curl -s -o /dev/null -w "%{http_code} " http://localhost:8010/api/reviews; done; echo
```

**Windows (PowerShell):** type `curl.exe`, with the `.exe`; plain `curl` in PowerShell is a
different command that rejects these flags.

```powershell
1..25 | ForEach-Object { curl.exe -s -o NUL -w '%{http_code} ' http://localhost:8010/api/reviews }; ""
```

Once you hit the limit your own requests are refused for a minute, so wait before the next
check. Twenty a minute is deliberately low for the check; a real app would pick a number from
its normal traffic.

## 8. Submit

Write this week's `PROCESS.md` entry: who did which step, **which of the seven your project
already had and which you added**, and what is blocked. Commit and push, by pull request.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| The headers are missing after writing the middleware | It is not registered. The `append` line in `bootstrap/app.php` is the fix. |
| The headers are on `/api/reviews` but not on a 404 | The middleware is on a route group instead of global. Use `$middleware->append(...)`, not `->web(...)` or `->api(...)`. |
| The audit names `symfony/mime` or another package you never asked for | A transitive dependency. `composer update symfony/mime` with the command in step 6 moves it past the advisory without touching anything else. |
| The injection check answers 500 | Usually a `sort` or `q` parameter reaches the query unvalidated; step 3. The error is in `docker compose logs app`. |
| The loop prints `200` 25 times | The limit is defined but not applied. `$middleware->throttleApi();` in `bootstrap/app.php` is the fix. |
| `Rate limiter [api] is not defined` | The `RateLimiter::for('api', …)` line is missing from `boot()`, or it names something other than `api`. |
| Every request is `429` for everyone, behind a proxy | Behind a proxy `$request->ip()` is the proxy's address. Ask in office hours; do not remove the limit. |
| `composer: not found` | Your `api/Dockerfile` does not install it. The pantry example's does; or run the audit on your laptop with the `docker run ... composer:2` image and `composer audit --locked`. |
