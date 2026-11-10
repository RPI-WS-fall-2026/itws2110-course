# Teaching the week 12 login lab

[Student instructions](README.md) · [Worked answers](answers/README.md)

| Part | When | Use |
|---|---|---|
| Lecture: logging in, sessions vs. tokens, OAuth, SSO | Tue 11/10 (session 21) | the deck |
| The team login lab | Fri 11/13 | the main event: [`../README.md`](../README.md) |
| These drills | Fri 11/13, after the team lab | each idea from Tuesday on a small app; drill 8 is the policy practice for Homework 9, and all eight are the warm-up for Homework 10 |

The drills are ungraded (the Check result buttons are the feedback). Students do them after
the team lab, or while waiting on a teammate. Two good ways to use class time: demo drills 3 and
6 on the projector at the start of Friday (four minutes, and they make the team lab's check 7
concrete), and demo drill 8's "Try it" (the admin's 404) right before teams start HW 9's policy:
it is the moment a scope stops being enough.

## Before class

- Build on the teaching machine: `docker compose watch` in `inclass/wk12/laravel`. **Port 8020**,
  compose project `itws2110-wk12`, so it runs beside week 11 (8000) and a team project (8010).
- `docker compose run --rm test`: the starter fails **10 tests in 8 drills** (drill 1 and
  drill 5 have two each), each with a sentence naming the file to fix.
  `docker compose --profile instructor run --rm answers`: **10 passed**. Verified 11/10 (drill 8
  added; drills 1–7 first verified 11/6).
- The three scripted attacks were run against the starter and the answers on 11/6: `guess`
  finds Priya's password on guess 25 (starter) / is refused with 429 from guess 6 (answers);
  `delete 2` deletes (starter) / is sent to `/login` (answers); `admin` makes an admin that
  gets 200 on `/admin` (starter) / an ordinary user that gets 403 (answers).

## The eight, and the one idea under each

| Drill | Attack | Fix | The idea (OWASP 2025) |
|---|---|---|---|
| warm-up | delete `login-lab-session` (named from `APP_NAME`) in devtools; `document.cookie` shows only `XSRF-TOKEN`; `.includes('login-lab-session')` is `false`. If a student sees nothing, they typed in the cookie table's Filter box, not the Console | — | the cookie is only an id; the server holds the session; HttpOnly |
| 1 | `/leak` shows the users table: plain-text passwords | `'password' => 'hashed'` cast + `Hash::check` | A04 Cryptographic Failures: store a salted, slow hash |
| 2 | unknown email and wrong password get different messages | one message | A07 Authentication Failures: no account enumeration |
| 3 | `attack guess` tries 30 passwords, finds #25 | `RateLimiter::for('login')` + `throttle:login` | A07: limit automated guessing (week 11's drill 7, applied) |
| 4 | `attack delete 2` with no login deletes a to-do | move the route into the `auth` group | A01 Broken Access Control: hiding a button is not a check |
| 5 | register with an extra `role=admin` field | create from `validate()`; `role` off `#[Fillable]` | A01 (mass assignment) |
| 6 | Priya opens `/todos/2`, Alex's | `$request->user()->todos()->findOrFail($id)` | A01 (IDOR): logged in ≠ allowed; 404 for private data |
| 7 | Priya's search returns Alex's to-dos | start the search from the user | A01: every query must be scoped (why HW 10 asks for a global scope) |
| 8 | after drill 6, the admin clicks a to-do on `/admin` and gets 404; the new rule (admin may view and delete, not update) cannot be written as a query | `TodoPolicy` with `view`/`update`/`delete` returning `allow`, `deny` (403) or `denyAsNotFound` (404); `findTodo()` does `Todo::findOrFail` + `Gate::authorize($ability, $todo)` | A01: one place for the rule, deny by default; policies judge one record, scopes filter lists |

## Two design notes worth knowing

- **There is no session-fixation drill, on purpose.** Laravel's `Auth::login()` already
  migrates the session id (`SessionGuard::updateSession` calls `migrate(true)`), so a
  fixation attack cannot be made to work without bypassing Laravel's guard. The
  `$request->session()->regenerate()` the slides show is still right (it also rotates the CSRF
  token); just don't claim Laravel is vulnerable without it.
- **Drill 6 answers 404, the team project's policy answers 403.** Both are right, for different
  data: the to-do list is private (don't confirm an id exists), a team's reviews are public
  (the reader can see it exists; only the owner may edit). Saying this out loud connects the
  drill to HW 9's policy and to HW 10's per-user lists.
- **Drill 8 uses both answers in one policy.** Priya gets `denyAsNotFound()` (404, so drill 6's
  check still passes after drill 8 replaces its fix); the admin gets `deny()` (403) on update,
  because the admin can already see the to-do on `/admin`. It also deliberately avoids
  `before()`: the pantry's `ItemPolicy` uses `before()` to let the RA do anything, and the README
  asks why that would be wrong here (the admin may not update). Expect students to reach for it.
- Drill 8 changes `findTodo()` after drill 6 changed it. That is on purpose (the rule grew), but
  a student who skipped drill 6 sees the admin get 200 on `/todos/2` before any fix; the check
  still fails for them on the admin's toggle (no 403) and on Priya (no 404).
- After drill 1, rows written before the cast are plain text, and `Hash::check` on a non-bcrypt
  value throws (*does not use the Bcrypt algorithm*), so the login page 500s until
  `migrate:fresh --seed`. The README says so in the drill and in Troubleshooting; expect the
  question anyway.
