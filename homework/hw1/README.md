# Homework 1 — Build the LAMP stack

**Assigned:** Fri 9/4 · **Due:** Fri 9/11, 11:59 PM · **Weight:** individual

In class you ran MySQL by itself, then Apache by itself, then connected two containers by hand and declared the result in Compose. This is where you do the whole thing yourself, on an application that has to actually work.

**When you are done you will have a running LAMP stack** — Linux, Apache, MySQL, PHP — that a stranger can clone and start with one command, and that stores real submissions in a real database.

## Get the starter

Everything in [`starter/`](starter/) is yours to copy. Put it in `homework/hw1/` in
**your own private repository** — not here; you cannot push to the course repo.

```bash
cp -R ../itws2110-course/homework/hw1/starter/. homework/hw1/
```

Copying the files across in Finder or Explorer works just as well.

Your write-up goes in `homework/hw1/WRITEUP.md`, alongside them.

## What you are given, and what you write

| | |
|---|---|
| **Given** | `src/index.html`, `src/styles.css`, `src/app.js` — the survey form, complete. Front-end work starts in week 3; it is not what this assignment is testing. |
| **Given** | `src/db/config.php`, `src/db/connect.php` — reads credentials from the environment and hands you a PDO connection. |
| **You write** | `docker/mysql/init.sql`, `docker/php-apache/Dockerfile`, `docker-compose.yml`, `src/submit.php` |

Every file you write has a direct analogue in a Tuesday or Friday example. Go back and read them; that is what they are for.

---

## Task 1 — The database layer

Write `docker/mysql/init.sql` so that a fresh database comes up with one `survey` table. The column list is in the file's comments — types, nullability, and defaults all matter.

Verify by getting inside the container and looking, the way you did in Example 1:

```bash
docker compose exec db mysql -uappuser -p"$MYSQL_PASSWORD" app -e "DESCRIBE survey;"
```

**Done when** `DESCRIBE survey` shows every column with the right type.

## Task 2 — The web image

Write `docker/php-apache/Dockerfile`. It needs PHP, Apache, and the PDO MySQL driver — which the base image does not include.

**Done when** `docker compose build web` succeeds and `docker compose exec web php -m` lists `pdo_mysql`.

## Task 3 — Wire the tiers together

Write `docker-compose.yml`. Requirements are in the file's comments. Three of them are not optional and are worth stating twice:

- **The database publishes no ports.** Only the web container may reach it. If you can connect to it from your laptop, you have not finished.
- **Credentials come from `.env`**, which is gitignored. Copy `.env.example` to `.env` and change the password. A password committed to git is a zero on this task.
- **`web` waits for `db` to be healthy**, not merely started. You saw why in Example 5.

**Done when** `docker compose up --build` brings up both services and `docker compose ps` reports `db` as `healthy`.

## Task 4 — The PHP layer

Complete `src/submit.php`: validate on the server, insert with a **prepared statement**, and render a confirmation page that escapes everything it echoes.

Then prove your server-side validation is real by bypassing the browser entirely:

```bash
curl -i -X POST http://localhost:8080/submit.php -d "firstName=&goals="
```

**Done when** that returns `422` and writes no row, and a properly filled form returns a confirmation page and writes exactly one.

## Task 5 — Reproducible from a clean clone

The whole point of Docker is that this works on a machine that is not yours.

```bash
docker compose down -v
docker compose up --build
```

**Done when** that sequence, starting from no volumes, produces a working site with an empty `survey` table — no manual SQL, no hand-editing, no "oh you also have to...".

## Task 6 — Extend it through all four layers

Add **one survey question of your own** — something you wish we had asked. It touches every tier, which is the point:

1. **HTML** — the field, with a properly associated `<label>`
2. **JavaScript** — client-side validation, if you make it required
3. **SQL** — the column in `init.sql` *(and you will need `down -v`; you should know why by now)*
4. **PHP** — store it, and show it on the confirmation page

## Task 7 — Write it up

Create `homework/hw1/WRITEUP.md` in your own repository and answer:

- **What broke, and what fixed it.** At least one thing will. If nothing did, say so and say what you think you did differently.
- **Why is the database not publishing a port?** Two or three sentences.
- **Your question**, and why you chose it.
- **Which of the four layers would you be least comfortable debugging right now?** Answer honestly; it changes what I do in weeks 3–6.
- **AI Use Statement — required.** Which tools, what for, what you changed. Three sentences is enough. "I did not use AI" is a complete answer. Omitting it is an academic integrity violation.

---

## Grading

| | |
|---|---|
| Task 1 — schema | 15% |
| Task 2 — web image | 10% |
| Task 3 — Compose: no exposed db, env credentials, healthcheck ordering | 25% |
| Task 4 — validation, prepared statement, escaped output | 25% |
| Task 5 — clean-clone reproducibility | 10% |
| Task 6 — your question, all four layers | 10% |
| Task 7 — write-up and AI statement | 5% |

**Automatic zero on Task 3** if a working credential is committed to git. Rotate it and resubmit; the late penalty applies, the zero does not stand.

*Autochecked:* `docker compose up --build` from a clean clone; `DESCRIBE survey`; a POST with missing fields returns 422; a valid POST inserts exactly one row; `git log -p` contains no `.env`.

## Submission

Everything named above belongs in `homework/hw1/` in **your own private repository**.
Commit to `main` and push. That is the whole process — no branch, no pull request.

```bash
git add homework/hw1/
git commit -m "HW1: LAMP stack"
git push
```

Your submission is whatever is on `main` at 11:59 PM Friday. Commit as you go rather than
all at once — the history is part of what I read.

Do not commit `.env` — `homework/hw1/.gitignore` already excludes it. Check with
`git status` before you commit.

**Done when** someone who has never seen your repository can clone it, copy `.env.example` to `.env`, run one command, and fill out your survey.
