# Worked answers — week 11 security lab

[← The workshop](../README.md)

Full solved copies of the six files the drills change, at the same paths, plus the fixed
`composer.json`/`composer.lock` (drill 5) and `.env.example` (drill 4). Try each drill before
you look.

| File | Drill |
|---|---|
| `app/Http/Controllers/TodoController.php` | 1 (bound `where`) |
| `resources/views/todos/show.blade.php` | 2 (`{{ }}`) |
| `resources/views/todos/index.blade.php` | 3 (`@csrf`) |
| `bootstrap/app.php` | 3 (no `except`) and 6 (middleware registered) |
| `config/pantry.php` | 4 (`env(...)`) |
| `.env.example` | 4 (`PANTRY_API_KEY`) |
| `composer.json` / `composer.lock` | 5 (`php-jwt` removed) |
| `app/Http/Middleware/SecurityHeaders.php` | 6 (four headers) |

| # | The fix in one line |
|---|---|
| 1 | `Todo::where('title', 'like', "%$q%")->orderBy('title')->get()` |
| 2 | `{{ $todo->notes }}` instead of `{!! $todo->notes !!}` |
| 3 | add `@csrf` inside the add form; delete `validateCsrfTokens(except: ['todos'])` |
| 4 | `'api_key' => env('PANTRY_API_KEY')`, and `PANTRY_API_KEY=...` in `.env.example` and `.env` |
| 5 | `docker run --rm -v "$PWD":/app -w /app composer:2 composer remove firebase/php-jwt --no-install --no-scripts` (it was unused; run on the host so the lockfile here changes) |
| 6 | four `$response->headers->set(...)` in `SecurityHeaders`, then `$middleware->append(...)` |

To check them all without touching your own files: `docker compose run --rm answers`.
