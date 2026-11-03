# Security lab, week 11 — find it, then fix it

[← Week 11](../../../weeks/week-11.md) · [Teaching guide](TEACHING.md) · [Worked answers](answers/README.md)

Every app you have built this term had the same six holes closed for you by the framework,
and you never saw them. This week you see them open. One small to-do app — the same to-do
list as Homeworks 6 and 7 — ships with six real weaknesses. For each one you **run the
attack first**, watch it work, then make the one change that closes it.

| Drills | Group | You edit | Checked by |
|---|---|---|---|
| 1–2 | Input you don't trust | a controller, a view | the **Check result** button |
| 3–4 | Requests and secrets | a form, `bootstrap/app.php`, a config | the **Check result** button |
| 5–6 | The platform | the dependencies, a middleware | the **Check result** button |
| 7 | If you finish | a route, a provider | the **Check result** button |

The loop is the same as weeks 7 and 9: see the attack, change one thing, save, check,
explain it to a partner.

## Set up — once, before class

Everything runs in Docker. From the course repository root:

```bash
cd inclass/wk11/laravel
docker compose watch
```

The first run builds the image and downloads MySQL, so give it a few minutes; do it **before
class**. If the week 9 workshop or Homework 7 is still running, stop it first
(`docker compose down` in its folder): all three use port 8000. When the terminal says
`Watch enabled`, open **http://localhost:8000**. That's the drills page.

Leave that terminal running. Each save prints `Syncing`, and the next page load has your change.

Port 8000 taken? `APP_PORT=8001 docker compose watch`, then use http://localhost:8001.

## A two-minute tour

| Path | What lives there |
|---|---|
| `app/Http/Controllers/TodoController.php` | The to-do pages. **Drills 1 and 3.** |
| `resources/views/todos/` | The to-do views. **Drills 2 and 3.** |
| `bootstrap/app.php` | Where middleware is turned on and off for every route. **Drills 3 and 6.** |
| `routes/web.php` | The routes. **Drill 6** puts a middleware on one of them, then takes it off again. |
| `config/pantry.php` | A config file with a secret in it. **Drill 4.** |
| `app/Http/Middleware/` | Does not exist yet. **Drill 6** generates a middleware into it with `artisan`. |
| `composer.json` / `composer.lock` | The packages the app depends on. **Drill 5.** |
| `tests/Feature/Drills/` | The checks. Each drill's **Check result** runs one of these. |

The drills page at http://localhost:8000 has, for every drill: **See the attack** (do this
first), **Why it works**, and **The fix**. Open it and read drill 1 before you change anything.

---

## Input you don't trust — drills 1–2

### 1. SQL injection: a search box that reads the users table · 8 minutes

Open `/todos` and search for this:

```
' UNION SELECT id, email, 0 FROM users WHERE email LIKE '%
```

The list fills with every user's email. Your search box just read a table it was never meant
to touch — and the same trick reaches password hashes. The search text was pasted straight
into the SQL. In `TodoController::index()`, change the raw query to a bound one:

```php
$todos = Todo::where('title', 'like', "%$q%")->orderBy('title')->get();
```

**Done when:** the check passes — the `UNION` search returns nothing, and an ordinary search
(`?q=rice`) still works.
**Explain:** the value you typed did not change, but now it cannot change the query. What is
different about where the value travels? (You met this in week 3: `prepare()` and `?`.)

### 2. XSS: a note that runs in everyone's browser · 6 minutes

Open `/todos/3`. The "Pantry potluck" note holds a `<script>` that pops an alert naming your cookies and what it can now do,
typed by whoever last edited the shared list. The starter page **runs** it. In
`resources/views/todos/show.blade.php`, change `{!! $todo->notes !!}` to `{{ $todo->notes }}`.

**Done when:** the check passes — the note shows as text, `<script>` and all, and does not run.
**Explain:** `{{ }}` is Blade's default and `{!! !!}` is the exception. Why would a template
engine ever offer `{!! !!}` at all? Name one case where you would need it.

---

## Requests and secrets — drills 3–4

### 3. CSRF: another site adds to-dos to your list · 7 minutes

Open **http://127.0.0.1:8000/attack** — `127.0.0.1`, not `localhost`. The app you use is at
`localhost:8000`; to your browser `127.0.0.1:8000` is a different website, which is the point.
The page pretends to be that other website, and without you clicking anything it posts a new
to-do to your list. Two things let it: the add-to-do form has no token, and `bootstrap/app.php`
switched the check off for `/todos`. Fix both:

- In `resources/views/todos/index.blade.php`, add `@csrf` as the first line inside the add form.
- In `bootstrap/app.php`, delete the `$middleware->validateCsrfTokens(except: ['todos'])` line.

**Done when:** the check passes — the form carries a token and the route is no longer excluded.
Reload `http://127.0.0.1:8000/attack`: now it gets **419 Page Expired**.
**Explain:** the fix is a secret token in the form. Why can your own form include it, but the
attacker's page cannot? (Your project's React front end solves the same problem a different
way — the Sanctum reading.) And why does the lab insist on `127.0.0.1`? Because a page on the
*same* origin is not an attacker, and Laravel 13 takes the browser's word for that
(the `Sec-Fetch-Site` header) and skips the token check.

### 4. A secret committed in code · 5 minutes

Open `config/pantry.php`. There is an API key written right into a file that git tracks —
so everyone with the repository has the key, and deleting it later does not help, because git
keeps the history. Move it out:

- In `config/pantry.php`: `'api_key' => env('PANTRY_API_KEY'),`
- In `.env.example` (committed) and `.env` (not committed), add a line:
  `PANTRY_API_KEY=pantry_demo_key_not_real_0000`

**Done when:** the check passes — the key is gone from `config/pantry.php`, which reads it from
`env()`, and `.env.example` records the name.
**Explain:** `.env` is in `.gitignore`. Why does the secret go there and not in the config file,
when both are just files on your laptop? What is different about git?

---

## The platform — drills 5–6

### 5. A dependency with a published advisory · 6 minutes

In a second terminal:

```bash
docker compose exec app composer audit
```

It reports that `firebase/php-jwt 5.5.1` has a published advisory, one rated **critical**. You
did not write that package, but it ships inside your app. It is not even used here, so remove it.
Run this one on your own machine, not inside the app container, so that **your**
`composer.json` and `composer.lock` change (the container only holds a copy of them):

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer remove firebase/php-jwt --no-install --no-scripts
```

Changing `composer.lock` rebuilds the image under `docker compose watch` (`Rebuilding` in that
terminal) — wait for it to finish before you check.

**Done when:** the check passes — the lockfile no longer pins a vulnerable `php-jwt`. Run
`composer audit` again: it is clean.
**Explain:** your app installs a few hundred packages you have never read. What does `composer
audit` (or `npm audit`) actually compare your packages against, and what can a package's
install script do on your laptop? (This week's npm reading is about exactly that going wrong.)

### 6. Security headers · 8 minutes

The app sends no security headers, so the browser guesses — and its guesses favour the
attacker. This drill is the Laravel [*Middleware*](https://laravel.com/framework/docs/13.x/middleware)
page, done on this app: define a middleware, put it on one route, then on every route.

**1. Generate it.** In a second terminal. The lab image has `artisan`, and the `test` service
shares your folder, so the new file lands in `app/Http/Middleware/` on your machine:

```bash
docker compose run --rm test php artisan make:middleware SecurityHeaders
```

Open `app/Http/Middleware/SecurityHeaders.php`: one `handle` method that passes the request on
with `return $next($request);` and does nothing else.

**2. Set the headers.** `$next($request)` returns the response. Keep it, set the four headers
on it, then return it:

```php
$response = $next($request);

$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('Content-Security-Policy', "default-src 'self'");
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

return $response;
```

**3. Put it on one route.** In `routes/web.php`, add it to the to-do list only:

```php
Route::get('/todos', [TodoController::class, 'index'])
    ->middleware(\App\Http\Middleware\SecurityHeaders::class)
    ->name('todos.index');
```

Look at both answers:

```bash
curl -si http://localhost:8000/todos | head -20
```

```bash
curl -si http://localhost:8000/ | head -20
```

The to-do list carries the four headers; the drills page carries none. Press **Check result**:
it fails and says exactly that. A route middleware runs for that route.

**4. Put it on every route.** Take the `->middleware(...)` line out of the route again and
register the middleware once, in `bootstrap/app.php`:

```php
$middleware->append(\App\Http\Middleware\SecurityHeaders::class);
```

Now every response carries the headers — the drills page, the to-do list, and a URL that does
not exist. `append` puts it at the end of the global stack, which is where "after the
response" middleware belongs.

The four headers, and what each tells the browser to refuse:

| Header | Value | Refuses |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | guessing a file's type (MIME sniffing) |
| `X-Frame-Options` | `DENY` | showing your page inside another site's frame (clickjacking) |
| `Content-Security-Policy` | `default-src 'self'` | loading scripts from anywhere but your own origin |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | ever using plain HTTP again |

**Done when:** the check passes — every response carries all four headers. See them yourself:
open the browser's Network tab, reload, click the request, read the Response Headers.
**Explain:** these four are the four MDN guides you read. Pick one and say, in a sentence, the
attack it stops. Then: why did step 3 fail the check and step 4 pass it?

---

### 7. If you finish: rate limiting · 5 minutes

Nothing in the app counts requests. In a second terminal:

```bash
for i in $(seq 1 25); do curl -s -o /dev/null -w '%{http_code} ' 'http://localhost:8000/todos?q=guess'; done; echo
```

Twenty-five `200`s. A script can search as fast as it likes. Point the same loop at a login
form and it is a password guesser; week 12 closes that one the same way. The fix is a
middleware you do not write, with an argument: `throttle:search`. Two edits.

Name the limit, in `app/Providers/AppServiceProvider.php` inside `boot()` (the `use` lines
are already there):

```php
RateLimiter::for('search', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
```

Put it on the route, in `routes/web.php`:

```php
Route::get('/todos', [TodoController::class, 'index'])
    ->middleware('throttle:search')
    ->name('todos.index');
```

Run the loop again: twenty `200`s, then `429`. The to-do page itself says *Too Many
Requests* until the minute is up — that is the cost of a limit, and why it is keyed on the
address rather than on everyone. (`throttle:20,1` does the same without naming the limit;
naming it lets you choose the key and reuse it.)

**Done when:** the check passes — search 21 in a minute is refused with a `Retry-After`.
**Explain:** the search is keyed on the address. What would you key a login limit on, and
why? (Week 12's answer: the email *and* the address, together.)

## Hand it in — end of class

**Hand-in: push your copy of the workshop to your own repository at the end of class** — with
whatever you got through. Unfinished drills are fine; it counts toward participation, not
correctness.

Copy the `laravel` folder from `inclass/wk11/` in the course repo and paste it into
`inclass/wk11/` in your own repository (Finder or Explorer is fine), then commit and push.
Kept going after class? Copy, paste and push again — the latest push counts.

## When something breaks

| What you see | First thing to check |
|---|---|
| The first `docker compose watch` takes minutes | Normal, once: it is downloading PHP, MySQL and the packages |
| `port is already allocated` | The week 9 workshop or Homework 7 is on 8000. Stop it, or `APP_PORT=8001 docker compose watch` |
| Drill 1 still fails after you edit | The check also runs an ordinary search; make sure `?q=rice` still lists matching to-dos |
| Drill 2: the note is gone, not escaped | You deleted it instead of escaping it. Keep `{{ $todo->notes }}` so the text still shows |
| Drill 3 passes the form check but not the route check | You added `@csrf` but left the `except` line in `bootstrap/app.php`. Delete that line too |
| Drill 3: the attack page still adds a to-do after the fix | You opened it at `localhost`. Open it at `http://127.0.0.1:8000/attack` — a same-origin page is let through on the browser's word |
| Drill 4 check fails on `.env.example` | Add `PANTRY_API_KEY` to `.env.example`, not only to `.env` |
| Drill 5 still fails after `composer remove` | Wait for the image to finish rebuilding (`Rebuilding` in the watch terminal), then check. If you ran it with `docker compose exec`, only the container's copy changed: run the `docker run … composer:2 …` command from drill 5 instead |
| Drill 6: the Check result buttons stopped working | Reload the drills page. If it is still dead, your `Content-Security-Policy` value blocks `/workshop.js`: use `default-src 'self'` |
| Drill 6: `make:middleware` says the file already exists | You ran it twice. Open the file it made the first time |
| Drill 6: headers on `/todos` but the check fails | That is step 3. The middleware is on one route; step 4 appends it in `bootstrap/app.php` so it runs on every route |
| Drill 6: headers not showing anywhere | You wrote the middleware but did not `append` it in `bootstrap/app.php` |
| Drill 7: `Rate limiter [search] is not defined` | The route names the limit but `boot()` does not define it. Add the `RateLimiter::for('search', …)` line |
| Drill 7: the to-do page says Too Many Requests | The limit works and you hit it. Wait a minute, or raise `perMinute(20)` while you work |
| `419 Page Expired` on the add form after drill 3 | Correct and expected on a stale tab: reload the page to get a fresh token, then add |

Don't edit the tests to get a PASS.

## Verify the workshop

For instructors:

```bash
docker compose run --rm test       # the starter: all six drills fail, each on its own assertion
docker compose run --rm answers    # the worked answers, on a throwaway copy: all six pass
```

## Official references

- [OWASP Top 10:2025](https://top10.owasp.org/2025/) — the names for all six of these.
- [SQL Injection Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html) — drill 1.
- [XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html) · [Blade: Displaying Data](https://laravel.com/docs/13.x/blade#displaying-data) — drill 2.
- [Laravel CSRF Protection](https://laravel.com/docs/13.x/csrf) — drill 3.
- [Laravel Configuration: Environment](https://laravel.com/docs/13.x/configuration#environment-configuration) — drill 4.
- [composer audit](https://getcomposer.org/doc/03-cli.md#audit) · [npm audit](https://docs.npmjs.com/cli/v11/commands/npm-audit) — drill 5.
- [Laravel Middleware](https://laravel.com/framework/docs/13.x/middleware) · [MDN: Practical security implementation guides](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides) — drill 6.
- [Laravel Rate Limiting](https://laravel.com/framework/docs/13.x/routing#rate-limiting) — drill 7.
