# Homework 1 — Wire up the LAMP stack

**Assigned:** Fri 9/4 · **Due:** Fri 9/11, 11:59 PM · **Individual**

In class you ran MySQL by itself, then Apache by itself, connected two containers by hand,
and then watched Compose declare all of that in one file — for a Node and MongoDB app.
You never saw a finished PHP + MySQL Compose file. **That is this assignment.**

You are given a complete, working web application: a survey form, the JavaScript that
validates it, and the PHP that stores it. **None of it runs**, because there is nothing to
run it on. You write the two Docker images and the Compose file that bring it up, and you
seed the database it needs.

## Get the starter

Everything in [`starter/`](starter/) is yours to copy. Put it in `homework/hw1/` in
**your own private repository** — not here; you cannot push to the course repo.

```bash
cp -R ../itws2110-course/homework/hw1/starter/. homework/hw1/
```

Copying the files across in Finder or Explorer works just as well. Your write-up goes in
`homework/hw1/WRITEUP.md`, alongside them.

## What you are given, and what you write

| | |
|---|---|
| **Given, complete** | `src/` — `index.html`, `styles.css`, `app.js`, `submit.php`, `db/config.php`, `db/connect.php`. The application. Read `submit.php` and `db/config.php`; do not edit them. |
| **You write** | `docker/mysql/Dockerfile` · `docker/mysql/init.sql` · `docker/php-apache/Dockerfile` · `docker-compose.yml` |

Each stub tells you exactly what it needs and which in-class example shows the pattern.

---

## Task 1 — The database image

`docker/mysql/Dockerfile` and `docker/mysql/init.sql`. The image bakes in the schema, the
way Example 2 did, and **seeds it** with at least three obviously-fake rows so the site
has data before any real person submits.

Verify by getting inside and looking, as in Example 1:

```bash
docker compose exec db mysql -uappuser -p<your password> app -e "DESCRIBE survey; SELECT COUNT(*) FROM survey;"
```

**Done when** `DESCRIBE survey` shows every column with the right type and the count is
your seed count.

## Task 2 — The web image

`docker/php-apache/Dockerfile`. PHP, Apache, the PDO MySQL driver the base image lacks,
and a `COPY` of `src/`.

**Done when** `docker compose build web` succeeds and `docker compose exec web php -m`
lists `pdo_mysql`.

## Task 3 — Wire them together

`docker-compose.yml`. Requirements are in the stub. Three are worth stating twice:

- **The database publishes no ports.** Only `web` may reach it. If you can connect from
  your laptop, you are not done.
- **Credentials come from `.env`**, which is gitignored. Copy `.env.example`, change the
  password. A password committed to git is a zero on this task.
- **`web` waits for `db` to be *healthy*,** not merely started. You saw why in Example 5E.

Run it with `docker compose watch`, then edit `src/index.html` — change the heading — and
confirm the change appears without a rebuild.

**Done when** `docker compose watch` brings up both services, `docker compose ps` shows
`db` as `(healthy)`, and <http://localhost:8080> serves the survey, whose confirmation page
already reports your seed rows.

## Task 4 — Reproducible from a clean clone

```bash
docker compose down -v
docker compose watch
```

**Done when** that sequence — from no volumes — yields a working site with exactly your
seed rows in `survey`. No manual SQL, no hand edits, no "oh, you also have to…".

## Task 5 — Add a third service

Add [Adminer](https://hub.docker.com/_/adminer) to `docker-compose.yml`: image `adminer:5`,
published on `${ADMINER_PORT:-8081}`, waiting on `db` the same way `web` does. Open it,
log in as `appuser`, and look at your table.

This is four or five lines. The point is that a third service costs almost nothing once
the first two are declared properly.

## Task 6 — Write it up

The starter includes `WRITEUP.md`. Fill it in — it is already in `homework/hw1/` after
you copy the starter across. **Three reflection questions**, a short paragraph each:

1. **`watch`** — what happened when you saved a file, and why `COPY src/` is still in the
   Dockerfile even though edits appear without a rebuild.
2. **Compose readiness** — what the healthcheck asks, and what you would have seen on first
   boot without `condition: service_healthy`.
3. **`down` vs `down -v`** — why editing `init.sql` and rebuilding does not change your
   seed rows, and what that says about where schema and data each live.

Plus one line on what broke, and the **AI Use Statement — required.** Which tools, what
for, what you changed. "I did not use AI" is a complete answer.

---

## Grading

| | |
|---|---|
| Task 1 — MySQL image, schema, seed | 25% |
| Task 2 — web image | 15% |
| Task 3 — Compose: no exposed db, env credentials, healthcheck ordering, watch | 30% |
| Task 4 — clean-clone reproducibility | 10% |
| Task 5 — Adminer | 10% |
| Task 6 — write-up and AI statement | 10% |

**Automatic zero on Task 3** if a working credential is committed to git. Rotate it and
resubmit; the late penalty applies, the zero does not stand.

*Autochecked:* `docker compose up --build` from a clean clone; `DESCRIBE survey`;
`SELECT COUNT(*)` equals your seed count; `docker compose port db 3306` returns nothing;
a valid POST inserts exactly one row; `git log -p` contains no `.env`.

## Submission

Everything above belongs in `homework/hw1/` in **your own private repository**. Commit to
`main` and push. That is the whole process — no branch, no pull request.

```bash
git add homework/hw1/
git commit -m "HW1: LAMP stack"
git push
```

Your submission is whatever is on `main` at 11:59 PM Friday. Commit as you go rather than
all at once — the history is part of what I read.

Do not commit `.env` — `homework/hw1/.gitignore` already excludes it. Check with
`git status` before you commit.

**Done when** someone who has never seen your repository can clone it, copy `.env.example`
to `.env`, run one command, and fill out your survey.
