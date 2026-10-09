# Homework 6 — The to-do list, built on the server

**Assigned Friday 10/9 · due Friday 10/23, 11:59 PM**

The same to-do list as Homework 5, with the work moved to the other side. In Homework 5 the
browser ran your React code and built the page. Here Laravel builds each page on the server
and sends finished HTML: routes, a controller, and Blade templates. No React, and no
JavaScript at all.

The starter runs, and one page already works: the list at `/todos`. You move it into a
controller, add a page for a single to-do, add a filter, and connect the pages with named
routes. Every part is something you did in the
[week 7 workshop](../../inclass/wk7/laravel/README.md); the table below says which drill.

Expect **three to four hours**. You have two weeks because 10/16 is not a class day and
Quiz 1 is 10/13. The to-dos are read-only and there is no database yet: forms and
Eloquent come in weeks 9 and 10.

---

## Set up

Copy the starter into your repository and start it. From your repository's root:

```bash
cp -R ../itws2110-course/homework/hw6/starter/. homework/hw6/
cd homework/hw6
docker compose watch
```

The first run builds the image and takes a few minutes. When the terminal says
`Watch enabled`, open **http://localhost:8010** and click **To-dos**. Leave that terminal
running: each time you save, it prints `Syncing`, and the next page load has your change.

Port 8010 taken? `APP_PORT=8011 docker compose watch`, then use http://localhost:8011.

Read these four files before you change anything. They are short.

| File | What it is | You |
|---|---|---|
| `routes/web.php` | The routing table. `/` and `/todos` are there | **edit** |
| `app/Http/Controllers/TodoController.php` | The controller, empty so far | **edit** |
| `resources/views/todos/index.blade.php` | The list page, working | **edit** |
| `resources/views/todos/show.blade.php` | The page for one to-do | **create** |
| `resources/views/components/layout.blade.php` | The shared layout | **edit one line** (Part 4) |
| `WRITEUP.md` | Part 5 | **edit** |
| `app/Todos/TodoList.php` | The to-dos: `all()` and `find($id)` | read; **leave alone** |
| `tests/` | The checks | read; **leave alone** |

Each to-do is an array: `['id' => 1, 'title' => 'Buy rice', 'done' => false, 'due' => '2026-10-12', 'notes' => '...']`.

**Don't type a to-do into a page.** Everything a page shows about a to-do has to come from
`TodoList`. The grader runs your app with its own `TodoList.php`, holding different to-dos
with different ids, so a page that says "Buy rice" because you wrote "Buy rice" won't pass.

---

## The five parts

| # | What you build | The workshop drill that practices it | Points |
|---|---|---|---|
| 1 | The list, answered by a **controller** | 6 | 15 |
| 2 | A **page for one to-do**, with a real 404 | 2, 3, 4, 5, 8 | 30 |
| 3 | A **filter** read from the query string | 9 | 25 |
| 4 | **Named routes**, and links written with `route()` | 7 | 15 |
| 5 | Write-up | | 15 |

Do them in order. Each one builds on the one before.

### 1. A controller for the list · 15

The `/todos` route in `routes/web.php` does its work in a function written right there.
Move that function's body into `TodoController`'s `index()` method, and replace the route
with one line that points at it:

```php
Route::get('/todos', [TodoController::class, 'index']);
```

The route's function got its to-dos by asking for them: `function (TodoList $todos)`. A
controller method asks the same way: `public function index(TodoList $todos)`. Laravel
sees the type and hands one over. You never write `new TodoList`.

**Done when:** `/todos` looks exactly as it did, and the Part 1 checks pass.

### 2. A page for one to-do · 30

Add a route `GET /todos/{id}` that points at a new `show()` method, and a new view,
`resources/views/todos/show.blade.php`. For to-do 1 the page shows:

- the title, in an `<h1>`
- `Status: active` or `Status: done`
- `Due: 2026-10-12`
- the notes — or `No notes.` when the notes are an empty string

Also:

- **Use the layout**, with the to-do's title as the tab title: `<x-layout :title="$todo['title']">`.
  The colon in `:title` means "this is PHP, not a string".
- **An id that isn't in the list is a 404.** `$todos->find($id)` returns `null` when there is
  no such to-do. Try `/todos/99`.
- **`/todos/abc` is a 404 too**, not an error page. Add `->whereNumber('id')` to the route:
  then a non-number doesn't match the route at all.
- **Print with `{{ }}`.** To-do 3's notes contain a `<b>` tag. It should show up on the page
  as the characters `<b>cold</b>`, not as bold text.

Declare the method as `show(TodoList $todos, int $id)`. The `{id}` in the URL arrives in `$id`.

### 3. The filter · 25

`/todos?status=active` lists only the to-dos that aren't done. `/todos?status=done` lists
only the ones that are. No status, `status=all`, or anything else lists everything.

- Read the status from the request: add `Request $request` to `index()`'s parameters and
  use `$request->query('status')`. **Not `$_GET`** — it works in the browser and fails the
  checks, for the reason drill 9 gave.
- Above the list, say how many are showing: `<p>Showing 4 of 6</p>`.
- Add three links — **All**, **Active**, **Done** — inside `<nav aria-label="Filter">`.
  Active goes to `?status=active`, Done to `?status=done`, All to plain `/todos`.
- The link for the filter you're looking at gets `aria-current="page"`. Only that one. On
  plain `/todos`, that is All. (The stylesheet already colors it.)

Decide which to-dos to show **in the controller**, and pass the view what it needs: the
to-dos to list, the total, and the current status. `array_filter()` keeps the items of an
array that a function says yes to.

This is Homework 5's filter. There it was `useState('all')`; Part 5 asks where it lives now.

### 4. Named routes and links · 15

Name the two routes `todos.index` and `todos.show`, then use the names for every link:

- In the list, each **title is a link** to that to-do's page:
  `route('todos.show', ['id' => $todo['id']])`
- The page for one to-do has a link reading **Back to the list**: `route('todos.index')`
- In the layout, the **To-dos** link in the nav bar uses `route('todos.index')` instead of
  the typed-in `/todos`
- Write the filter links the same way: `route('todos.index', ['status' => 'active'])`

The checks look for the address `route()` writes, so a link typed in by hand
(`href="/todos/3"`) doesn't count.

### 5. Write-up · 15

Answer the two questions in the `WRITEUP.md` that came with the starter — a short paragraph
each — then the AI Use Statement at the bottom. Keep the numbered headings.

---

## Check your work

In a second terminal, from `homework/hw6/`:

```bash
docker compose run --rm test
```

These are the checks the grader runs: 21 of them, in `tests/Feature/Hw6/`. Two pass before
you start (the home page and the list). Each of the others names what it wants, and the
files are worth reading — they are the HTTP tests from Friday's class. To run one part:

```bash
docker compose run --rm test php artisan test --filter Part2
```

To see the routing table you've built:

```bash
docker compose exec app php artisan route:list
```

---

## Grading

| | |
|---|---|
| Part 1 — the list is answered by `TodoController@index` | 15% |
| Part 2 — the page for one to-do: content 16, escaping 5, layout 3, 404s 6 | 30% |
| Part 3 — the filter: the right to-dos 13, the count 4, the links 8 | 25% |
| Part 4 — two named routes 4, links written with `route()` 11 | 15% |
| Part 5 — write-up and AI Use Statement | 15% |

The grader runs the same checks twice: with the to-dos you see, and with its own. The
points come from the second run.

---

## Submission

Commit and push `homework/hw6/`:

```
homework/hw6/
├── app/Http/Controllers/TodoController.php   Parts 1, 2, 3
├── routes/web.php                            Parts 1, 2, 4
├── resources/views/
│   ├── todos/index.blade.php                 Parts 3, 4
│   ├── todos/show.blade.php                  Part 2 (new)
│   └── components/layout.blade.php           Part 4
├── WRITEUP.md                                Part 5
└── ...                                       everything else from the starter, unchanged
```

Don't commit `vendor/` or `.env`. The starter's `.gitignore` already leaves them out, and
with Docker neither exists on your laptop anyway.

---

## When something breaks

Laravel shows its own error page. Read the first line: it names the problem and usually
the file and line.

| What you see | First thing to check |
|---|---|
| The first `docker compose watch` takes minutes | Normal, once. It is downloading PHP and the packages |
| `port is already allocated` | Something else is on 8010: `APP_PORT=8011 docker compose watch` |
| Your edit doesn't show up | Is the watch terminal still running? Each save should print `Syncing` |
| `Target class [TodoController] does not exist` | `routes/web.php` needs `use App\Http\Controllers\TodoController;` at the top |
| `Class "App\Http\Controllers\Request" does not exist` | The controller needs `use Illuminate\Http\Request;` at the top |
| `View [todos.show] not found` | The file is `resources/views/todos/show.blade.php` — check the folder and the spelling |
| `Undefined variable $todo` | The controller didn't pass it: `view('todos.show', ['todo' => $todo])` |
| `Trying to access array offset on null` | You're reading `$todo['title']` for an id that doesn't exist. Check for `null` and `abort(404)` first |
| `Route [todos.show] not defined` | The route has no `->name('todos.show')` yet |
| `Missing required parameter for [Route: todos.show]` | `route('todos.show')` needs the id: `route('todos.show', ['id' => $todo['id']])` |
| `syntax error, unexpected token "endif"` | A Blade block isn't closed, often a missing `@endforeach` |
| `/todos/abc` shows a TypeError page | Add `->whereNumber('id')` to the route |
| The filter works in the browser but its checks fail | You read `$_GET`. Use `$request->query('status')` |
| A check passes for you but you're told it failed in grading | Something on the page is typed in, not read from `TodoList` |

Using [Laravel Herd](../../inclass/wk7/laravel/README.md#optional-laravel-herd-instead-of-docker)
instead of Docker? Same steps as the workshop: `composer install`, `cp .env.example .env`,
`php artisan key:generate`, then `php artisan serve` and `php artisan test`.
