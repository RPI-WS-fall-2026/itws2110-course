# Deploy drills, week 13 — ready for strangers

[← Week 13](../../../weeks/week-13.md) · [Teaching guide](TEACHING.md) · [Worked answers](answers/README.md)

Tuesday 11/17, with the lecture. The to-do app from weeks 11 and 12 works on a laptop. Here
its container is given **production's settings**, and four things about the app are still a
laptop's. For each one you see it break, then make the one change that fixes it. Then you
build the app's **production image** and run it the way a server would.

**What you do**

1. Start the app (below) and open http://localhost:8030.
2. Do drills 1 to 4 in order. Each one says what you see when the change works.
3. Build and run the production image (the last section).
4. Push your copy to your own repository at the end of class.

**What counts:** the push, for participation. Nothing here is graded.

| Drills | Group | You edit |
|---|---|---|
| 1–2 | A copy that strangers use | `config/app.php`, `bootstrap/app.php` |
| 3 | Deploys that keep the data | `start.sh` |
| 4 | Behind the proxy | `bootstrap/app.php` |
| — | The production image | nothing: you build it and look |
| 5–6 | If you finish | a new migration, `pipeline/deploy.yml` |

[Homework 10](../../../homework/hw10/README.md) makes changes 2, 3 and 4 to your team's
project and puts it on Azure, so today is the practice run on a small app.

## Set up — once, before class

From the course repository root:

```bash
cd inclass/wk13/laravel
docker compose watch
```

When the terminal says `Watch enabled`, open **http://localhost:8030**: the drills page.
Leave that terminal running. Commands in the drills go in a **second terminal**, in the same
folder.

## What is different about this container

Open `docker-compose.yml` and find the `environment:` block under `app`:

```yaml
environment:
  APP_ENV: production
  APP_DEBUG: "false"
  LOG_CHANNEL: stderr
```

Those three lines are the container's environment, and a real environment variable wins
over the same line in `.env`. Nothing in that block is inside the image. On Azure, the
platform fills in the same kind of block for your app.

---

## A copy that strangers use — drills 1–2

### 1. An error page that shows strangers your code · 5 minutes

Open `/boom`: a page with a bug. You get Laravel's debug page, with the SQL, the database's
host and name, and every file in the stack. Now ask the container what its environment says:

```bash
docker compose exec app printenv APP_DEBUG
```

`false`. The environment said no, and the app showed everything anyway, because
`config/app.php` has `true` typed into it. Change that line to ask the environment:

```php
'debug' => (bool) env('APP_DEBUG', false),
```

**Done when:** `/boom` says only "500 Server Error".
**Explain:** why is the second argument `false` and not `true`? What happens to a copy whose
environment forgot to say?

### 2. Nobody can ask the app whether it is ready · 3 minutes

Open `/up`: 404. A platform starts a new copy of your app beside the old one and has to
decide when visitors may be sent to it. It asks the new copy a question, and this app has no
answer. In `bootstrap/app.php`, inside `withRouting( ... )`, add:

```php
health: '/up',
```

**Done when:** `/up` says "Application up".
**Explain:** a copy crashes on its first line. With a health route, who sees the crash: your
visitors, or only the platform?

---

## Deploys that keep the data — drill 3

### 3. Every start erases the database · 6 minutes

1. Log in as `alex@example.edu` / `password` and add a to-do: **Deploy on Friday**.
2. Start the container again, as every deploy does:

   ```bash
   docker compose restart app
   ```

3. When the first terminal says `Server running`, reload `/todos`. Your to-do is gone.

`start.sh` is what the container runs at every start, and its first command is
`migrate:fresh --seed`: drop every table, rebuild, write the sample rows. Change that line to:

```sh
php artisan migrate --force
```

Save. Watch copies the file in and restarts the container for you. Add the to-do again,
restart again, reload: still there.

**Done when:** a to-do survives `docker compose restart app`.
**Explain:** the app container was restarted both times. Where were the rows, that they
could survive it? (Look for `volumes:` in `docker-compose.yml`.)

---

## Behind the proxy — drill 4

### 4. The app does not know it is on https · 6 minutes

On Azure the browser speaks https to Azure's edge, a proxy. The edge holds the certificate,
decrypts the request, and hands it to your container as plain http, with headers that say how
it really arrived. Send a request the way the edge would (one line):

```bash
docker compose exec app curl -s -H "X-Forwarded-Proto: https" -H "X-Forwarded-Host: todo.example.edu" http://localhost:8000/request
```

`/request` prints what the app believes: scheme `http`, host `localhost:8000`, and a link
that starts `http://`. It ignored both headers. In `bootstrap/app.php`, inside
`withMiddleware`, add:

```php
$middleware->trustProxies(at: '*');
```

Run the `curl` line again.

**Done when:** it says scheme `https`, host `todo.example.edu`, and a link that starts
`https://todo.example.edu`.
**Explain:** Laravel ignores those headers unless told otherwise. Why is that the safe
default? And why is `'*'` acceptable for a container that only Azure's edge can reach?

---

## The production image · 15 minutes

So far the app has run in the laptop image (`Dockerfile`): Composer, PHPUnit, and
`php artisan serve`. A server runs a different image, built from the same files. Open
`Dockerfile.prod` beside `Dockerfile`; the three differences are listed at its top.

**Predict first:** the production image is built from your files as they are now. Which of
your four fixes will it have?

1. In the second terminal (leave `watch` running in the first):

   ```bash
   docker compose -f docker-compose.prod.yml up --build
   ```

   The first build downloads FrankenPHP and PostgreSQL. When the log stops scrolling, the
   app is at **http://localhost:8035**: a second copy of the app, on its own port, with its
   own empty database.

2. Look at it, in this order:

   | Open | You should see | Because of |
   |---|---|---|
   | http://localhost:8035/up | `Application up` | drill 2 |
   | http://localhost:8035/boom | `500 Server Error`, nothing more | drill 1 |
   | http://localhost:8035/register | an empty app: make an account, add a to-do | production never runs the seeder |

   In a third terminal, `curl -I http://localhost:8035/up` shows `Server: FrankenPHP Caddy`:
   a real web server, not artisan's.

3. Ctrl+C in the second terminal, then the same `up` line again, **without** `--build`.
   Reload `/todos`. You are still logged in, and your to-do is still there.

4. Done looking: `docker compose -f docker-compose.prod.yml down` (the data is kept; add
   `-v` to throw it away).

**Explain:** the image's last line runs `migrate --force` at every start, the same line as
your fixed `start.sh`. Why are you still logged in after the restart? (Find `SESSION_DRIVER`
in `docker-compose.prod.yml`.)

---

## If you finish — drills 5–6

### 5. A new column, without emptying the table · 8 minutes

Do drill 3 first. Log in (on 8030) and click **Sort by priority**: "500 Server Error". Drill 1
is why the page says no more. The reason went to the log:

```bash
docker compose logs app --since 2m
```

Scroll up to the line that starts `production.ERROR`: *Unknown column 'priority'*. The
sorting code was deployed; the table it needs was never changed.

Make a **new file**, `database/migrations/2026_11_17_000001_add_priority_to_todos_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Schema::table changes a table that already exists. Its rows stay, and get the default.
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('priority')->default('normal');
        });
    }

    // The way back takes away only what up() added.
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
```

Then run it, as the next deploy's `start.sh` would:

```bash
docker compose exec app php artisan migrate --force
```

Run it a second time: `Nothing to migrate`. Reload the page: sorted, and "Deploy on Friday"
is still there, at normal priority.

**Done when:** Sort by priority shows your list, with "Deploy on Friday" still in it.
**Explain:** how did the second `migrate` know there was nothing to do? And why would adding
the column to the `create_todos` migration not have worked on a deployed database?

### 6. A failing test that deploys anyway · 4 minutes

Open `pipeline/deploy.yml`: two jobs, `test` and `deploy`. Predict first: a push breaks a
test. What happens to the live site?

GitHub starts every job at the same moment unless one says it depends on another. Under
`deploy:`, indented like `runs-on:`, add:

```yaml
    needs: [test]
```

**Done when:** `deploy:` has the `needs: [test]` line, at the same indent as `runs-on:`.
**Explain:** when `test` fails, `deploy` is skipped. What is the site running afterwards?

---

## Hand in

Copy the `laravel` folder from `inclass/wk13/` in the course repo into `inclass/wk13/` in
your own repository, then commit and push. Unfinished drills are fine: the push is the
participation.

## Troubleshooting

| What you see | Do this |
|---|---|
| Port 8030 is taken | `APP_PORT=8031 docker compose watch`, then use http://localhost:8031 |
| The page does not load after a restart | wait for `Server running` in the first terminal; MySQL takes a few seconds |
| Drill 1: `/boom` still shows the debug page | check the line reads `env('APP_DEBUG', false)`, save, reload |
| Drill 3: the login page says the account does not exist | the database is empty (you ran `down -v` after the fix): `docker compose exec app php artisan db:seed --force` |
| The image: `/up` is 404, or `/boom` shows the debug page, on 8035 | that drill is not done in your files, and the image is a copy of your files. Fix it, then `up --build` again |
| The image: port 8035 is taken | `PROD_PORT=8036 docker compose -f docker-compose.prod.yml up --build`, then use 8036 |
| The image: the build stops at `composer install` | your machine is offline or short on disk space; `docker system df` shows the space |
| Drill 5: `Nothing to migrate`, but there is still no column | the file was saved half-written and already ran. `docker compose exec app php artisan migrate:rollback --step=1 --force`, finish the file, `migrate --force` again |
| Drill 5: `Duplicate column name 'priority'` | the column is already there; the migration ran. Reload the page |
| You want to start over | `docker compose down -v`, then `docker compose watch` |
