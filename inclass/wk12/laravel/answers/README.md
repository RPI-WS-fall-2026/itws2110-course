# Worked answers — week 12 login lab

[← The lab](../README.md)

Full solved copies of the files the drills change, at the same paths. Try each drill before
you look.

| File | Drills |
|---|---|
| `app/Models/User.php` | 1 (`'password' => 'hashed'`), 5 (no `role` in `#[Fillable]`) |
| `app/Http/Controllers/AuthController.php` | 1 (`Hash::check`), 2 (one message), 5 (create from `validate()`) |
| `app/Providers/AppServiceProvider.php` | 3 (the `login` limit) |
| `routes/web.php` | 3 (`throttle:login`), 4 (the delete route inside `auth`) |
| `app/Http/Controllers/TodoController.php` | 6 (`findTodo` through the user), 7 (the search through the user) |

| # | The fix in one line |
|---|---|
| 1 | `'password' => 'hashed'` in `casts()`; `Hash::check($credentials['password'], $user->password)`; then `migrate:fresh --seed` |
| 2 | one `if (! $user \|\| ! Hash::check(...))` with `'These credentials do not match our records.'` |
| 3 | `RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'\|'.$request->ip()))`; `->middleware('throttle:login')` on `POST /login` |
| 4 | move `Route::delete('/todos/{id}', …)` inside `Route::middleware('auth')->group(…)` |
| 5 | `$fields = $request->validate([...]); User::create($fields);` and `#[Fillable(['name', 'email', 'password'])]` |
| 6 | `return $request->user()->todos()->findOrFail($id);` |
| 7 | `$request->user()->todos()->where('title', 'like', "%$q%")` |

To check them all without touching your own files: `docker compose --profile instructor run --rm answers`.
