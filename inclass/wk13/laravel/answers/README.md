# Worked answers — week 13 deploy drills

[← The drills](../README.md)

Solved copies of the files the drills change, at the same paths. Try each drill first.

| # | File | The change in one line |
|---|---|---|
| 1 | `config/app.php` | `'debug' => (bool) env('APP_DEBUG', false),` |
| 2 | `bootstrap/app.php` | `health: '/up',` inside `withRouting( ... )` |
| 3 | `start.sh` | `php artisan migrate --force` in place of `migrate:fresh --seed --force` |
| 4 | `bootstrap/app.php` | `$middleware->trustProxies(at: '*');` inside `withMiddleware` |
| 5 | `database/migrations/2026_11_17_000001_add_priority_to_todos_table.php` | if you finish: a new migration, `Schema::table('todos', ...)` adds `string('priority')->default('normal')`; `down()` drops the column |
| 6 | `pipeline/deploy.yml` | if you finish: `needs: [test]` under `deploy:` |
