# Homework 7 — Start your project's Laravel back end

**Due Friday 11/6, 11:59 PM. Team homework, in your team repo. Everyone commits.**

Your midterm is laid out like
[`project/pantry-example-midterm`](../../project/pantry-example-midterm/): a `web/` folder with
React pages reading fake data, and a `docker-compose.yml`. The goal of the project is the layout
of [`project/pantry-example-final`](../../project/pantry-example-final/): the same `web/`, plus an
`api/` folder with a Laravel back end and a MySQL database. This homework is the map from one to
the other. You start a new Laravel project in `api/` and build its database side: the table, the
model, the starting data, and the routes your `API.md` promises. Log in and the write endpoints
come in Homework 8.

There is no starter and no autograder this time. We go through each team's `api/` together in
class the week after it is due. Bring it running.

The example below uses a resource called `Review` at `/api/reviews`. Use your own name.

## What we look at in class

| # | What | Where |
|---|---|---|
| 1 | `docker compose watch` brings up `web`, `app` and `db`; http://localhost:8010/up answers | `docker-compose.yml`, `api/Dockerfile` |
| 2 | A migration for your resource, with every field in `API.md` and a `user_id` foreign key | `api/database/migrations/` |
| 3 | A model with `user()`, `User` with `reviews()`, and a factory | `api/app/Models/`, `api/database/factories/` |
| 4 | A seeder with three users (one admin) and your midterm's fake data | `api/database/seeders/DatabaseSeeder.php` |
| 5 | Five routes in `route:list`; `GET` list and `GET` one return your `API.md` JSON; the other three answer 501 | `api/routes/api.php`, the controller |
| 6 | This week's `PROCESS.md` entry | `PROCESS.md` |

## Two kinds of commands

Nothing is installed on your laptop, so every command runs in a container.

**Generators** (`make:…`, `install:api`) only write files. Run them in a plain PHP container
that shares your `api/` folder, so the files land where your editor can see them. From your
repo's root:

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan make:model Review
```

**Database commands** (`migrate`, `tinker`, `route:list`) need MySQL, so they run inside the
app that `docker compose watch` started:

```bash
docker compose exec app php artisan migrate:status
```

## 1. A new Laravel project in `api/`

Composer is PHP's npm. This makes a fresh Laravel app in `api/`, the same way `npm create vite`
made `web/`. It takes a few minutes.

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 create-project laravel/laravel api
```

A new Laravel app has no API routes file. This adds `routes/api.php` and installs Sanctum,
which Homework 8 uses for log in.

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan install:api
```

Laravel needs a Dockerfile to run in Compose. The pantry final's is written for exactly
this: it installs the dependencies, and on every start rebuilds the tables, seeds them,
and serves. Copy it and its `.dockerignore`:

```bash
cp ../itws2110-course/project/pantry-example-final/api/Dockerfile ../itws2110-course/project/pantry-example-final/api/.dockerignore api/
```

A new Laravel app uses SQLite by default. Point it at MySQL: in `api/.env.example`, change

```
DB_CONNECTION=sqlite
```

to

```
DB_CONNECTION=mysql
```

Add two services to your `docker-compose.yml`, under `web`: `db` (MySQL) and `app`
(Laravel). Copy them, and the `volumes:` block, from the pantry final's
[`docker-compose.yml`](../../project/pantry-example-final/docker-compose.yml). Change the
`image:` name to your project's. You do not need its `unit`, `api` or `e2e` services yet.

Start it. `app` waits for MySQL, builds the tables, seeds, and serves on port 8010.

```bash
docker compose watch
```

http://localhost:8010/up

## 2. Migration

A migration describes a table in code, so every laptop and the grader build the same one.
This one command makes the model, its migration, a factory, and a controller with the five
API methods already named.

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan make:model Review -mfc --api
```

In the new file in `api/database/migrations/`, add one line per field in `API.md`. The
first is the owner: a foreign key to `users`, so the database refuses a row whose owner
does not exist.

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('title', 120);
$table->unsignedTinyInteger('rating');
$table->text('body')->nullable();
```

Your project has two roles. In `…_create_users_table.php`, under `password`, add:

```php
$table->string('role')->default('user');
```

Rebuild MySQL, then confirm both migrations ran.

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan migrate:status
```

## 3. Model and factory

The model is the class your controller talks to: one object is one row, and Eloquent
writes the SQL. `Fillable` lists the fields a request may set. `user_id` is not in it,
because the owner will come from the login in Homework 8. `user()` is how you write
`$review->user->name` instead of a join. The generated file already has `HasFactory`; add
the two imports, the `Fillable` line and the method, so the whole file reads:

`api/app/Models/Review.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'rating', 'body'])]
class Review extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

The other direction, in `api/app/Models/User.php`. Add the import at the top and the method
inside the class:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```

```php
public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
```

The factory makes fake rows for the seeder. One fake value per field; leave `user_id`
out, it is set with `->for($user)`. In `api/database/factories/ReviewFactory.php`:

```php
public function definition(): array
{
    return [
        'title' => fake()->sentence(3),
        'rating' => fake()->numberBetween(1, 5),
        'body' => fake()->paragraph(),
    ];
}
```

## 4. Seeder

The seeder is where your midterm's fake data (`web/src/api.js` or similar) moves to. It
runs on every start, so every laptop has the same rows. Replace `run()` in
`api/database/seeders/DatabaseSeeder.php`: three users, one of them an admin, then your
rows across at least two owners. Eight rows at least. The factory's password is `password`.

```php
$alex = User::factory()->create(['name' => 'Alex Rivera', 'email' => 'alex@example.edu']);
$priya = User::factory()->create(['name' => 'Priya Shah', 'email' => 'priya@example.edu']);
$admin = User::factory()->create(['name' => 'Jordan Lee', 'email' => 'admin@example.edu', 'role' => 'admin']);

$alex->reviews()->create(['title' => 'Great TA', 'rating' => 5, 'body' => 'Explained everything twice.']);
Review::factory()->count(4)->for($priya)->create();
```

Rebuild MySQL and look at the rows.

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
> App\Models\Review::with('user')->get();
```

## 5. Routes

This is `API.md` becoming real. One line declares all five routes, each mapped to a method
of the controller from step 2. In `api/routes/api.php`:

```php
use App\Http\Controllers\ReviewController;

Route::apiResource('reviews', ReviewController::class);
```

The two read endpoints work now. `with('user')` loads every owner in one query instead of
one per row. `show(Review $review)` lets Laravel find the row from the id, or answer 404
before your code runs. The three write endpoints answer `501 Not Implemented` until
Homework 8 adds log in and validation. In `api/app/Http/Controllers/ReviewController.php`:

```php
public function index()
{
    return ['data' => Review::with('user')->orderByDesc('created_at')->get()];
}

public function show(Review $review)
{
    return ['data' => $review->load('user')];
}

public function store(Request $request)
{
    return response()->json(['message' => 'Not yet: Homework 8.'], 501);
}

public function update(Request $request, Review $review)
{
    return response()->json(['message' => 'Not yet: Homework 8.'], 501);
}

public function destroy(Review $review)
{
    return response()->json(['message' => 'Not yet: Homework 8.'], 501);
}
```

List the routes, then open the real thing. Compare the JSON with the example response in
`API.md`.

```bash
docker compose exec app php artisan route:list --path=api
```

http://localhost:8010/api/reviews · http://localhost:8010/api/reviews/1 · http://localhost:8010/api/reviews/999

## 6. Submit

Write this week's `PROCESS.md` entry: who did which step, what broke, what is blocked.
Commit and push, by pull request. `api/.gitignore` already leaves out `vendor/` and `.env`.
