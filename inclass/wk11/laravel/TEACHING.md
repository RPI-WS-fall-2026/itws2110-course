# Teaching the week 11 security lab

[Student instructions](README.md) · [Worked answers](answers/README.md)

| Part | Course session | Suggested allocation |
|---|---|---|
| Lecture: the Top 10 and the six holes | 19, Tue 11/3 | the deck ([`slides/19_security.pdf`](../../slides/19_security.pdf)) |
| Drills 1–6 | 20, Fri 11/6 (Security Lab) | ~10 min setup recap, then ~50 min for the six drills in pairs, with drills 1 and 3 demoed on the projector |

The Tuesday lecture introduces every one of these six as an attack and a one-line fix; Friday
is where students do it themselves. Run the lab after the lecture, not instead of it.

## Before class

- **Build on the teaching machine**: `docker compose watch` in `inclass/wk11/laravel`. It pulls
  MySQL as well as building the app. Ask students to do it before Friday.
- **Port 8000 is shared** with the week 9 workshop and Homework 7. Anyone who left one running
  gets `port is already allocated`. Say so at the start.
- **Run both checks**: `docker compose run --rm test` (the starter — eight failing assertions
  across the six drills, each on its own assertion; drill 1's "ordinary search" guard passes,
  which is correct) and `docker compose run --rm answers` (the worked answers on a throwaway
  copy — all six pass, 9 assertions). Verified: starter is red on every drill, answers green.
- Teach from a copy if you will solve drills live, so the course copy stays a starter.
- **Two things found in the 11/2 dry run, both fixed in the starter.** Drill 5's `composer remove`
  must run on the host (the `docker run … composer:2 … --no-install --no-scripts` form in the
  README): `docker compose exec app composer remove` changes only the container's copy, so the
  check passes there but the student's `composer.lock` -- and their push -- stays vulnerable.
  And drill 6's `Content-Security-Policy: default-src 'self'` blocks inline scripts, so the
  drills page's Check buttons and `/attack`'s auto-submit now live in `public/workshop.js` and
  `public/attack.js`; an inline `<script>` in either page would die the moment drill 6 is fixed.
  Worth saying out loud in drill 6: the Check button still works *because* its script is a file
  from this origin.

## The shape of the lab: attack first, then fix

Every drill on the page leads with **See the attack**. That is the pedagogy, not decoration:
a student who has watched `/attack` add a to-do to their own list understands CSRF in a way no
definition delivers. Insist they run the attack before they read the fix. The three worth
doing together on the projector are 1, 3 and 6.

## The six, and the one idea under each

| Drill | Attack | Fix | The idea (OWASP 2025) |
|---|---|---|---|
| 1 | `' UNION SELECT id, email, 0 FROM users WHERE email LIKE '%` in the search box dumps user emails | `where('title','like',...)` — a bound parameter | A05 Injection: the value must travel separately from the SQL |
| 2 | a seeded note holds `<script>`, and the page runs it | `{{ }}` instead of `{!! !!}` | A05 (XSS): encode for the context you write into |
| 3 | `/attack`, a stand-in for another site, POSTs to `/todos` on its own | `@csrf` in the form **and** remove the CSRF `except` | A01 Broken Access Control: a token the attacker can't read |
| 4 | an API key sits in `config/pantry.php`, a tracked file | `env('PANTRY_API_KEY')` + `.env.example` | A02 Misconfiguration: secrets live in `.env`, not git |
| 5 | `composer audit` flags `firebase/php-jwt 5.5.1` (critical) | `composer remove firebase/php-jwt` | A03 Supply Chain: you ship code you didn't write |
| 6 | no security headers; the browser guesses in the attacker's favour | generate a `SecurityHeaders` middleware, four headers, route then global | A02 Misconfiguration: tell the browser what to refuse |
| 7 (if they finish) | nothing counts requests; a script searches, or guesses, as fast as it likes | `RateLimiter::for('search', …)` + `throttle:search` on the route | A07 Authentication Failures: a limit is a middleware with an argument |

## The one thing to explain twice: why two drills check files, not behaviour

Drills 1, 2 and 6 are checked by asking the app for a page and reading the answer — the attack
either works or it doesn't. Drills 3, 4 and 5 are checked by reading the file the student
changed (the form's token, the config line, the lockfile). Two reasons, both worth saying:

- **CSRF (drill 3) cannot be tested through a request here.** Laravel's own test runner skips
  the live CSRF middleware (so that every *other* feature test doesn't have to carry a token),
  which means a PHPUnit request can't tell protected from unprotected. So the check reads the
  two artifacts the fix changes: the `_token` field in the form, and the `except` list in
  `bootstrap/app.php`. Show students the real thing instead: in the browser, after the fix,
  reload `http://127.0.0.1:8000/attack` and watch the POST get a `419 Page Expired`.
  **It must be `127.0.0.1`, not `localhost`** (found in the 11/2 dry run): Laravel 13's
  `PreventRequestForgery` middleware lets any POST through whose `Sec-Fetch-Site` header says
  `same-origin`, token or no token, so an attack page on the app's own origin is never rejected.
  The attack page's form now posts to an absolute `http://localhost:<port>/todos`, and the drills
  page's Open link for drill 3 points at `127.0.0.1`. Two bonus points to make while you are
  there: the session cookie is `SameSite=Lax`, so a 2026 browser does not even send it on that
  cross-site POST (the starter still creates the to-do because `store()` needs no login); and the
  `Sec-Fetch-Site` check is "the framework got better" from Tuesday's first predict, in the flesh.
- **Secrets and dependencies are facts about the repository, not the running app** (drills 4
  and 5). A secret is dangerous because it is *in git*; a dependency is dangerous because it is
  *in the lockfile*. The check reads exactly those.

That contrast is itself a lesson: some security properties are about what the system does, and
some are about what is written down.

## The pauses that matter

- **Drill 1, together:** paste the `UNION` payload on the projector and read the emails aloud
  off the to-do list. Then add one word — change the raw string to `where(...)` — and run the
  same payload: nothing. Ask what changed about the *value*. Nothing did; what changed is that
  it can no longer be read as SQL. Tie it back to week 3's `prepare()`.
- **Drill 2:** `{{ }}` vs `{!! !!}`. Ask why Blade ships `{!! !!}` at all. The honest answer —
  you sometimes have HTML you trust and want rendered — is also the trap: "trust" is the whole
  question, and the seeded note is a stranger's.
- **Drill 3, together:** open `http://127.0.0.1:8000/attack`. A to-do appears that nobody typed.
  Ask: how is this not a legitimate request? The server cannot tell, from the request alone, which
  page sent it — that is the whole of CSRF. (Honest footnote for 2026: the browser's `SameSite=Lax`
  default withholds the session cookie on a cross-site POST, and the `Sec-Fetch-Site` header names
  the origin; the token is the defense that does not depend on the browser.) Then the token: your form can include it, the attacker's
  page cannot read it. Connect to the pantry project's React front end, which solves this with
  Sanctum's `XSRF-TOKEN` cookie + `X-XSRF-TOKEN` header — the same token, carried by hand.
- **Drill 4:** ask who, in a team of four, now has the key once it is committed. Everyone. And
  everyone who ever forks or clones, forever, because `git log -p` has it. Deleting it in a new
  commit does not remove it from history. This is why a leaked key must be *rotated*, not just
  deleted — a good aside.
- **Drill 5:** run `composer audit` live. The advisory is real (`firebase/php-jwt` before 7.0).
  Ask how many of the ~80 installed packages anyone in the room has read. Zero. That is the
  supply chain: `audit` is how you find out a package you never chose, pulled in by one you did,
  has a known hole. Mention the npm reading (Shai-Hulud): an install script is ordinary code
  that runs on your laptop the moment you `npm install`.
- **Drill 6:** open the Network tab, reload, show the response has none of the four headers.
  This drill is the Laravel Middleware doc page done live: generate the class with
  `make:middleware` (through the `test` service, so the file lands on the host), set the four
  headers, put it on the `/todos` route only and curl both pages, then move it to `append` in
  `bootstrap/app.php` and show all four everywhere. The Check result fails on purpose after the
  route step and says so. Each header is a single instruction to the browser; map them to the
  four MDN guides from the reading.
- **Drill 7, for pairs who finish:** the curl loop prints 25 `200`s; after the two edits, 20
  then `429`. The point to land: they did not write this middleware; the colon passes it an
  argument, and the limit is named once in the provider. Week 12 does the same on the login
  route, keyed on email and address. Warn them: once they hit the limit, their own to-do page
  is refused for a minute.

## How the checks work

Same mechanism as weeks 7 and 9. The **Check result** button calls
`WorkshopController::check()`, which runs `vendor/bin/phpunit --filter DrillNTest` in a separate
process with `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. `phpunit.xml` forces the same
two values, so `docker compose run --rm test` behaves identically and needs no MySQL container.
The file-reading checks (drills 3–5) read `base_path(...)`, which under `docker compose watch`
is the synced copy of the student's files; a `composer.*` change (drill 5) triggers an image
**rebuild**, so wait for `Rebuilding` to finish before checking drill 5.

## The thread to the rest of the course

- Drill 1 is week 3's `prepare()`/`?` and week 9's bound `where()`, now shown as the attack it
  prevents.
- Drill 2 is week 7's drill 4 (escaping is Blade's default), now with a live `<script>`.
- Drill 3 is last week's CSRF reading made concrete, and the exact mechanism the pantry
  project's `api.js` uses with Sanctum.
- Drills 4–6 are new this week, and they are what **Homework 8** (harden your own api/: the same
  six) asks students to do to their own project: find these, fix them, and say in PROCESS.md
  what they found.

## Saltzer and Schroeder, on the summary slide

The Tuesday deck closes by mapping each drill onto one of the 1975 design principles: parameter
binding and escaping are *complete mediation* (every input checked, every time); `.env` and
least-privilege DB users are *least privilege*; secure defaults (headers, CSRF on) are
*fail-safe defaults*. The point to land: these are not six tricks, they are one disposition —
assume every input is hostile and every default should fail closed.

## Exit prompt

"Name one of today's six that the framework had already fixed for you before you ever saw it,
and say what you had to do to *un*fix it." (Every one: Blade escapes by default, CSRF is on by
default, Eloquent binds by default. The starter app turned each protection off. That is the
lesson — the framework's defaults are the security, and the bugs are where someone overrode
them.)
