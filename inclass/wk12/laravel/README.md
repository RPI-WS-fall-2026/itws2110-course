# Login lab, week 12 — break it, then fix it

[← Week 12 lab](../README.md) · [Teaching guide](TEACHING.md) · [Worked answers](answers/README.md)

**Not graded: the Check result buttons are your feedback.** Do these after the
[team lab](../README.md), or while you wait on a teammate. They show Tuesday's ideas work (and
fail) on a small app before you put them in your team project, and drill 8 writes the same kind
of policy class Homework 9 asks for. They are also the best warm-up for [Homework 10](../../../homework/hw10_optional_individual/README.md),
which builds the same app's login yourself.

It is the to-do app from week 11, with last week's six fixes already in, and a login added:
register, log in, log out, a list of your own, and an admin page. The login has seven holes,
one for each idea on Tuesday's slides. For each one you **run the attack first**, watch it
work, then make the one change that closes it, and press **Check result**. Drill 8 then changes
the rules and moves them all into one class, a **policy**.

| Drills | The question | You edit | Checked by |
|---|---|---|---|
| warm-up | What is a session cookie? | nothing: you look | — |
| 1–3 | **Who are you?** Storing passwords, the error message, guessing | the User model, `AuthController`, a provider, a route | the **Check result** button |
| 4–6 | **What may you do?** Logged out, the admin role, someone else's to-do | `routes/web.php`, `AuthController`, `TodoController` | the **Check result** button |
| 7 | The search | `TodoController` | the **Check result** button |
| 8 | **Write the rule once:** the admin may read any to-do, not change it | `app/Policies/TodoPolicy.php`, `TodoController` | the **Check result** button |

## Set up — once

```bash
cd inclass/wk12/laravel
docker compose watch
```

The first run builds the image and pulls MySQL: a few minutes. It runs on **port 8020**, so
it does not collide with week 11 (8000) or your team project (8010). When the terminal says
`Watch enabled`, open **http://localhost:8020**: the drills page. Leave that terminal running;
each save prints `Syncing`.

Port 8020 taken? `APP_PORT=8021 docker compose watch`, then use http://localhost:8021.

The seeded accounts, all with the same data for everyone:

| Email | Password | Who |
|---|---|---|
| `alex@example.edu` | `password` | ordinary user, to-dos 1–4 |
| `priya@example.edu` | `password` | ordinary user, to-dos 5–6 |
| `admin@example.edu` | `admin-pass` | the admin |

Two commands you will use in a **second terminal**, in the same folder:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

puts the tables back the way they started (after drill 1, and after an attack deletes something), and

```bash
docker compose exec app php artisan attack guess
```

runs one of the three scripted attacks (`guess`, `delete`, `admin`). The attacker is a
script, not a browser; read it in `app/Console/Commands/Attack.php` if you like.

---

## Warm-up: the cookie is the login · 5 minutes

1. Log in as `alex@example.edu` / `password`.
2. Open the developer tools: **Application** (Chrome, Edge) or **Storage** (Firefox, Safari)
   → **Cookies** → `http://localhost:8020`. Find `login-lab-session`. Note the **HttpOnly** tick.
   (Laravel names the cookie after the app: `APP_NAME="Login Lab"` in `.env` gives
   `login-lab-session`. Your team app's cookie has your app's name.)
3. Now ask what JavaScript on the page can see. Click the **Console** tab at the top of the
   developer tools (not the Filter box above the cookie table). At the `>` prompt, type each
   line and press Enter:

   ```js
   document.cookie
   document.cookie.includes('login-lab-session')
   ```

   The first prints one line in quotes starting `XSRF-TOKEN=eyJ…` (you may also see cookies
   from other apps you ran on `localhost`: cookies ignore the port). The second prints
   **`false`**. The table in step 2 had two cookies; JavaScript sees only one. HttpOnly hides
   the session cookie from every script, so an XSS script can't steal it. `XSRF-TOKEN` is
   left visible on purpose: it holds the CSRF token, which JavaScript has to read, not your login.
4. Delete the cookie and reload **My to-dos**. You are sent to log in.

**Explain:** the server still has the session (a file in `storage/framework/sessions`). What
did deleting the cookie take away? Then read `AuthController::logout()`: what are its three
lines for, if the browser forgets the cookie anyway?

---

## Who are you? — drills 1–3

### 1. Passwords stored as typed · 8 minutes

Open **http://localhost:8020/leak**. It is the `users` table as anyone with a copy of your
database sees it. Every password is there in plain text.

- In `app/Models/User.php`, add `'password' => 'hashed',` to `casts()`.
- In `AuthController::login()`, compare with `Hash::check($credentials['password'], $user->password)`
  instead of `!==`.
- In the second terminal, write the seeded rows again: `php artisan migrate:fresh --seed`
  (the full command is above). Reload `/leak`.

**Done when:** the check passes, and `/leak` shows `$2y$12$…` in every row.
**Explain:** Alex and Priya both chose `password`. Are their two hashes the same? Why not, and
why is that good? (Hint: think about a table of precomputed hashes of common passwords.)

### 2. Two error messages · 5 minutes

Log in as `nobody@example.edu`, then as `alex@example.edu` with a wrong password. Two different
messages: the form just told you which emails have accounts. In `AuthController::login()`,
replace the two `if` blocks with one:

```php
if (! $user || ! Hash::check($credentials['password'], $user->password)) {
    return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
}
```

**Done when:** the check passes.
**Explain:** Laravel's `Auth::attempt($credentials)`, which the pantry example uses, does this
lookup and this check in one call. Why does that make the two-message mistake hard to make?

### 3. Unlimited guesses · 7 minutes

```bash
docker compose exec app php artisan attack guess
```

Thirty common passwords against Priya's account. It finds hers on guess 25. Note that the
script fetched the login page first and sent its CSRF token: CSRF protection does not stop a
script. Last week's drill 7 does:

- In `app/Providers/AppServiceProvider.php`, un-comment the `RateLimiter::for('login', …)` line:
  five tries a minute for one email from one address.
- In `routes/web.php`: `Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');`

**Done when:** the check passes, and the attack gets `429` on guess 6 and gives up.
**Explain:** why is the limit keyed on the email *and* the address, not only the address?
(Hint: whom would a limit on the email alone let an attacker lock out?)

---

## What may you do? — drills 4–6

### 4. A route outside the login · 5 minutes

Log out. There is no Delete button anywhere: the pages only show it to someone logged in. Now:

```bash
docker compose exec app php artisan attack delete 2
```

Alex's to-do 2 is gone, deleted by a script that never logged in. In `routes/web.php`, the
`Route::delete(...)` line at the bottom sits outside the `Route::middleware('auth')` group.
Move it inside. `php artisan migrate:fresh --seed` brings the to-do back.

**Done when:** the check passes, and the attack is sent to `/login`.
**Explain:** your team project's `check-login.sh` asks this same question in check 7. What is
the API's version of "sent to `/login`", and why is it different for an API?

### 5. Register yourself as an admin · 6 minutes

Open `/register`, open the developer tools, right-click the `<form>` → **Edit as HTML**, add
`<input name="role" value="admin">` before the button, and register. You have an **Admin**
link and every user's list. (Or: `php artisan attack admin`.) Two fixes:

- In `AuthController::register()`, keep what `validate()` returns and create from that:
  `$fields = $request->validate([...]);` then `User::create($fields);`
- In `app/Models/User.php`: `#[Fillable(['name', 'email', 'password'])]` — no `role`.

**Done when:** both checks pass, and the attack reports an ordinary user and `/admin` → 403.
**Explain:** either fix alone stops this attack. Why do both?

### 6. Someone else's to-do, by its number · 6 minutes

Log in as `priya@example.edu`. Her list has to-dos 5 and 6. Type **http://localhost:8020/todos/2**
into the address bar: Alex's note about his midterm grade. Press **Mark done**. You are logged
in, so `auth` let you through, but nothing asked *whose* to-do it is. In
`TodoController::findTodo()`:

```php
return $request->user()->todos()->findOrFail($id);
```

**Done when:** the check passes, and `/todos/2` as Priya is a 404.
**Explain:** this is a 404, not a 403. Why does a private list say "not found" rather than
"forbidden"? Your team project answers the wrong user with a **403** from a policy. What is
different about your project's data?

---

### 7. The search box · 3 minutes

As Priya, search for the letter `e`. Alex's to-dos are in the results. In
`TodoController::index()`, start the search from `$request->user()->todos()`, like the list.

**Done when:** the check passes.
**Explain:** drills 6 and 7 are the same mistake in two places. With fifty queries, how would
you make sure none of them forgets? (Homework 10 asks the same question.)

---

## Write the rule once — drill 8

### 8. The admin may read any to-do, but not change it · 15 minutes

Do drill 6 first. Then a new rule arrives: the admin answers support requests ("I can't find my
to-do"), so the admin must be able to **read** any to-do, and **delete** one that breaks the
rules. But it is not the admin's list, so the admin may **not** mark it done.

**Try it.** Log in as `admin@example.edu` / `admin-pass`, open **/admin**, and click Alex's
*Email the TA*: **404**. Drill 6's fix says "only your own rows", and the admin owns none.

**Predict before you read on:** can you write this rule by changing the query in `findTodo()`?
(Try: what query lets the admin *read* to-do 2 but not *toggle* it, when both go through the
same `findTodo()`?)

You can't, because the answer depends on the *action*, not only on the row. Here is the whole
rule as a table, the same shape as Part 1 of `PROJECT_v3.pdf`:

| Action | Route | The owner | The admin | Anyone else |
|---|---|---|---|---|
| `view` | `GET /todos/{id}` | yes | yes | 404 |
| `update` | `PATCH /todos/{id}/toggle` | yes | **403** | 404 |
| `delete` | `DELETE /todos/{id}` | yes | yes | 404 |

A **policy** is a class that holds that table for one model. Laravel finds it by name
(`Todo` → `App\Policies\TodoPolicy`). One method per row; each gets the logged-in user and the
to-do, and returns one of three answers:

| The method returns | The browser gets | Use it when |
|---|---|---|
| `Response::allow()` | the page, as normal | the table says yes |
| `Response::deny('Why not.')` | **403** Forbidden | they may know it exists, but may not do this |
| `Response::denyAsNotFound()` | **404** Not Found | they should not learn that it exists |

**A worked row.** This is `view()`, the first row of the table, finished:

```php
public function view(User $user, Todo $todo): Response
{
    if ($todo->user_id === $user->id || $user->isAdmin()) {   // the owner, or the admin
        return Response::allow();
    }

    return Response::denyAsNotFound();   // anyone else: 404, like drill 6
}
```

`$user` comes from the session and `$todo->user_id` from the database. Nothing in it came from
the request, so nothing in it can be forged.

**Your turn, two files:**

1. In `app/Policies/TodoPolicy.php` (it is there, with every method saying `allow()`), write
   `view()` as above, then `update()` and `delete()` from their rows of the table.
2. In `TodoController::findTodo()`, look the to-do up in the **whole** table again, then ask
   the policy. `$ability` is already passed in: `'view'` from `show()`, `'update'` from
   `toggle()`, `'delete'` from `destroy()`.

   ```php
   $todo = Todo::findOrFail($id);      // 404 if no such to-do at all
   Gate::authorize($ability, $todo);   // asks TodoPolicy; throws the 403 or 404 for you
   return $todo;
   ```

**Done when:** the drill 8 check passes, **and drill 6's still does**: Priya still gets 404 for
Alex's to-do, now from the policy. As the admin, `/todos/2` opens, and **Mark done** answers 403.

**Explain:**
- The pantry's `ItemPolicy` has a `before()` method that lets the admin do anything. Why would
  `before()` be wrong in this policy?
- Drill 7's search still starts from `$request->user()->todos()`. Why can't the policy fix the
  search? (Hint: a policy is asked about one to-do you already have. What does a search have
  before it runs?)
- The **Mark done** button still shows for the admin, who then gets a 403. In `todos/show.blade.php`,
  `@can('update', $todo) … @endcan` would hide it, using the same policy. Why is hiding the
  button not enough on its own? (Drill 4.)

---

## Run every check at once

```bash
docker compose run --rm test
```

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| `port is already allocated` | Something is on 8020. `APP_PORT=8021 docker compose watch`. |
| After drill 1, logging in gives a 500 *does not use the Bcrypt algorithm* | The rows were written before the cast. Run `php artisan migrate:fresh --seed` in the second terminal. |
| `attack guess` says 429 from the very first guess | The limit is working and still counting from your last run. Wait a minute. |
| `attack delete` says *There is no to-do 2* | An earlier attack deleted it. `php artisan migrate:fresh --seed`. |
| Logged in, but `/todos/5` is a 404 as Alex | Correct after drill 6: 5 is Priya's. |
| The Check button says the check failed to run | Is the watch terminal still running? Reload the page. |
| Drill 8: the admin still gets 404 on `/todos/2` | `findTodo()` still starts from `$request->user()->todos()`. Drill 8 replaces drill 6's line with `Todo::findOrFail($id)` plus `Gate::authorize(...)`. |
| Drill 8: Priya now gets 403, not 404, and drill 6 fails | The policy returned `Response::deny()` for her. Anyone who is neither the owner nor the admin gets `Response::denyAsNotFound()`. |
| Drill 8: everyone may do everything | `Gate::authorize($ability, $todo)` is missing from `findTodo()`, so the policy is never asked. |
