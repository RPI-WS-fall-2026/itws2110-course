# Homework 10 — Your project, live on Azure

**Team homework, in your team repo. Assigned Fri 11/20 · class time Fri 11/20 and Tue 12/1 ·
due Tue 12/8, 11:59 PM (the week after Quiz 2). Everyone on the team commits something.**

On Tue 11/17 you made the to-do app ready for strangers and ran its production image on your
laptop. Now your team does the same to your project. By the end:

- your project runs at an `https://` address on Azure that anyone can open;
- a `git push` to `main` is what puts it there;
- it is submitted to the course platform, https://ta.biggermatrix.com, so it can join the
  showcase.

Expect **two to three hours**, most of it in class. You do not write the new files from
nothing: [`starter/`](starter/) has the ones you copy, and the pantry example
([`project/pantry-example-final`](../../project/pantry-example-final/)) shows each one in a
finished app.

## What is graded

| # | What | Steps | How it is checked |
|---|---|---|---|
| 1 | **The production image runs on your laptop** | 1–6 | The deploy check against `http://localhost:8090` says **0 failed** (8 passed the first time, 9 after step 6) |
| 2 | **A push deploys it** | 7–8 | `.github/workflows/deploy.yml` is in your repo, and the latest **deploy** run on `main` is green |
| 3 | **It works for strangers** | 9–10 | The deploy check against your Azure address says **11 passed** |
| 4 | **`README.md` says where it is** | 10 | Your Azure address and your last deploy-check output ([example](#what-to-put-in-readmemd)) |
| 5 | **It is submitted on the hub** | 11 | One submission for the team, every teammate's RPI email on it, your repo and `main`. Every hub check that must pass is green, except the two for the hub login ([which](#the-hub-submission)) |

After the due date the grader runs the deploy check against the address in your `README.md`,
looks at your repo's **Actions** tab, and opens your submission on the hub. You can run both
checks yourself as often as you like: they are the same ones.

**Two different checks.** The *deploy check* is a script, `check-deploy.sh`, with numbered
checks 1–11 ([below](#the-deploy-check)); "deploy check 7" on this page means its seventh. The
*hub check* is the list ta.biggermatrix.com shows on your submission; its lines have names,
not numbers ([below](#the-hub-submission)).

## Before you start

1. **Pull the course repo** (`git pull` in your `itws2110-course` folder), so you have
   `homework/hw10/check-deploy.sh` and the `starter/` files. Your team repo and the course repo
   should sit side by side in the same folder.
2. **Your Homework 9 login works.** Deploy checks 7, 8 and 10 register and log in through your
   `/api/register` and `/api/login` routes. Until those work, those three fail; the other
   eight can still pass.
3. **Docker Desktop is running.**

---

## The steps

Eleven steps. Steps 1–10 go in order, each building on the one before. Step 11 does not
depend on them: **submit on the hub on Friday**, and its checks turn green as your Part A and
Part B work reaches `main`.

**Split the work:** one teammate shares their screen and types Part A, another types Part B,
someone does step 11. Everyone watches the pipeline run.

**Friday 11/20 in class:** step 11, all of Part A, and if you can, your first green deploy
(step 8). **Tuesday 12/1, after the Quiz 2 review:** class time to finish and to get help.

| # | Where | What to do |
|---|---|---|
| | **Part A — the production image, on your laptop** | |
| 1 | the root of your repo | Copy three files from [`starter/`](starter/) next to `api/` and `web/`: `Dockerfile`, `.dockerignore` and `docker-compose.prod.yml`. |
| 2 | `api/routes/web.php` | Replace the `/` route with the fallback route from the example's [`web.php`](../../project/pantry-example-final/api/routes/web.php). It sends every path that is not `/api/*` to the React app, so a link like `/reviews/3` works when someone opens it directly. |
| 3 | `api/bootstrap/app.php` | Inside `withMiddleware`, under `statefulApi()`, add one line: `$middleware->trustProxies(at: '*');` (Tuesday's drill 4). Azure's proxy speaks https to the browser and plain http to your container, and adds headers saying how the visitor really arrived. This line tells Laravel to believe them: links and redirects then start with `https://`, and each visitor counts as their own address (so a login rate limit is per visitor, not one limit for everyone behind the proxy). Your Homework 8 `SecurityHeaders` line is already in this file; leave it. |
| 4 | a terminal | `docker compose -f docker-compose.prod.yml up --build`, then open http://localhost:8090. One container serves both your pages and your API. The database starts empty: register an account and add one row of your resource. |
| 5 | a second terminal | Run the [deploy check](#the-deploy-check) against `http://localhost:8090`. Expect **8 passed, 0 failed, 3 skipped** (checks 9–11 need https). |
| 6 | the first terminal | Ctrl+C, then `docker compose -f docker-compose.prod.yml up` again (no `--build`), and run the deploy check again. Expect **9 passed, 2 skipped**: check 10 now passes, because the account from the first run survived a restart. |
| | **Part B — on Azure** | |
| 7 | `.github/workflows/deploy.yml` | Copy it from [`starter/.github/workflows/deploy.yml`](starter/.github/workflows/deploy.yml). Commit everything from steps 1–7 and push to `main`. |
| 8 | GitHub → your repo → **Actions** → **deploy** | Watch the run: the `php` and `web` test jobs first, then `deploy`. When it is green, the run's summary shows your `https://` address. |
| 9 | a terminal | Run the deploy check against your `https://` address. Expect **10 passed, 1 skipped**. |
| 10 | any page in `web/src` | Change a word a visitor can see. Commit, push, wait for the pipeline, reload your address. Run the deploy check a third time: **11 passed**. Your data survived a deploy. Paste this output into `README.md` ([how](#what-to-put-in-readmemd)). |
| | **Part C — on the course platform** | |
| 11 | https://ta.biggermatrix.com | One teammate logs in with their RPI account and presses **Submit a project**, filled in as in [the table below](#the-hub-submission). The hub reads `main` on GitHub and lists its checks. Fix any line marked **must be fixed**, except the two for the hub login, push, and press **Check again**. |

You do not set up Azure yourself. The instructor has already made your team's Azure app and
put the `AZURE_*` secrets and `LAB_*` variables in your repository's settings, so the
pipeline can deploy without anyone holding a password.

## The deploy check

Run it from your team repo's root. The first argument is the address; the second is your
resource's path under `/api/` (for `/api/reviews`, write `reviews`; if you leave it out, it
uses `items`):

```bash
bash ../itws2110-course/homework/hw10/check-deploy.sh http://localhost:8090 reviews
```

On Azure, the same command with your address:

```bash
bash ../itws2110-course/homework/hw10/check-deploy.sh https://lab-s1-t1.<the rest of your address> reviews
```

It needs only `curl`. A fully green run on Azure looks like this:

```
PASS  1. GET /up -> 200: the container started and its migrations ran
PASS  2. GET / -> the React app (index.html from web/dist, not a Vite dev server)
PASS  3. GET /assets/index-B1x2y3.js -> 200, JavaScript: the server sends the built files itself
PASS  4. GET /a/page/that/only/react/knows -> the React app (the fallback route in routes/web.php)
PASS  5. GET /api/reviews -> 200 JSON: Laravel reached its database
PASS  6. GET /api/no-such-route -> 404 with no stack trace (APP_DEBUG is false)
PASS  7. POST /api/register -> 201: a row was written to the production database
PASS  8. GET /api/user with the cookie -> 200: the login survives between requests
PASS  9. the session cookie is Secure: it is never sent over plain http
PASS  10. the account from the last run (deploy-check-1795…@example.edu) still logs in: the deploy kept the data
PASS  11. Strict-Transport-Security: max-age=31536000; includeSubDomains: browsers will refuse plain http here
11 passed, 0 failed, 0 skipped
```

| Deploy check | What it proves | Where you saw it |
|---|---|---|
| 1 | The image starts, and `migrate --force` ran against the database | Tue 11/17, drills 2 and 3 |
| 2, 3, 4 | One container serves the React build, its files, and deep links | Tue 11/17, the production image |
| 5 | The settings reached the container as environment variables | Tue 11/17, "what is different about this container" |
| 6 | `APP_DEBUG=false`: an error tells a stranger nothing about your code | Tue 11/17, drill 1 |
| 7, 8 | Register, log in, and stay logged in at the deployed address | Homework 9 |
| 9 | Azure only: the session cookie is `Secure` (your Azure app has `SESSION_SECURE_COOKIE=true`) | Tue 11/17, https |
| 10 | Second run onward: the account from the last run is still there | Tue 11/17, drill 3 |
| 11 | Azure only: browsers are told to use https here for the next year (HSTS) | Homework 8's headers |

## What to put in `README.md`

Add this near the top of your team repo's `README.md`, with your address and the output of
your last run against it:

~~~markdown
## Live

https://lab-s1-t1.<the rest of your address>

```
PASS  1. GET /up -> 200: the container started and its migrations ran
...
11 passed, 0 failed, 0 skipped
```
~~~

## The hub submission

https://ta.biggermatrix.com is the course platform: the showcase where every team's project
will run, with RPI login. A project gets there through a submission: the hub checks the
repository, the instructor approves it, and the instructor deploys it. Step 11 is the first
part of that. **One teammate submits for the whole team** and fills in:

| Field | What to put |
|---|---|
| Course | ITWS 2110, this semester |
| Project name, Slug, Tagline, Description, Team | Your project, as you want it on the showcase. The slug is the short name used in its address (for example `study-rooms`). |
| Audience | Who the app is for |
| Teammates' emails | Each teammate's RPI email, one per line. Everyone listed sees the submission and its checks under **My submissions**. |
| Authentication | **RPI login through the hub** |
| Data sensitivity | **Internal: platform accounts, no protected records**, unless your app holds grades or other education records (then FERPA, and talk to the instructor) |
| Stack | **Laravel + React (PostgreSQL)** |
| GitHub repository, Branch to deploy | Your team repo's address, and `main` |
| Laravel folder, React folder | `api`, `web` |
| Contact email | Where the instructor's questions should go |

The hub then reads `main` and lists its checks by name, on your submission's page. These
nine are marked **must be fixed** when they fail; the rest are **advice**. Which ones this
homework grades:

| Hub check (as the hub names it) | What it looks for | Graded in HW 10 |
|---|---|---|
| Repository is on GitHub | The address is a GitHub repository | yes |
| Repository and branch exist | The hub can read your repository and `main` | yes |
| Laravel app in api/ | `api/composer.json` requires `laravel/framework` | yes |
| Logs in through the hub (rpi-platform/hub-auth) | `api/composer.json` requires the hub login package, and `packages/hub-auth-laravel/` is in the repo | no — later |
| api/.env.example lists the platform settings | `HUB_URL`, `HUB_CLIENT_ID`, `HUB_CLIENT_SECRET` and `DB_CONNECTION=pgsql` | no — later |
| No .env committed | `api/.env` is not in the repository | yes |
| React app in web/ | `web/package.json` has a `build` script | yes |
| Production Dockerfile at the repository root | A root `Dockerfile` that listens on 8080 | yes — step 1 |
| Lock files committed | `api/composer.lock` and `web/package-lock.json` | yes |

The two marked *later* are the RPI login through the hub, which your team adds before the
project goes on the showcase; the pantry example's
[`PRD` switch](../../project/pantry-example-final/README.md#logging-in-on-the-course-platform-the-prd-switch)
shows that change. They are not part of this homework, so expect them to stay red, and your
submission to show as not ready, until then.

The advice lines are not graded. One of them, **API routes**, will stay amber: it points
out your `/login` and `/register` routes, which you keep.

Red checks do not stop you submitting. Fix, push, and press **Check again**: the hub reads
`main` on GitHub each time. The person who submitted can edit the submission at any time,
and nothing is public until the instructor approves it.

## What is the same on Azure, and what is not

| | Your laptop (step 4) | Azure (step 8) |
|---|---|---|
| The image | built by `docker compose` from your `Dockerfile` | built by Azure's registry from the same `Dockerfile`, named after the commit |
| The database | PostgreSQL in a container | your own database on the lab's PostgreSQL server |
| The settings | written in `docker-compose.prod.yml` | set on your Container App by the instructor; `APP_KEY` and the database password are secrets |
| The address | `http://localhost:8090` | `https://lab-<your team>.….azurecontainerapps.io`; Azure holds the certificate |
| When nobody visits | keeps running | scales to zero; the next visit waits 10 to 40 seconds |

Your Azure address stays up for the rest of the term, and every push to `main` redeploys it.
Use it in your final presentation. The copy on the showcase is separate: the instructor
deploys it once your submission is approved.

**Optional, once you are green: break the gate on purpose.** Change one assertion in an
`api/tests` file so it fails, and push. Watch `php` go red and `deploy` get *skipped*, while
your address still shows the last good version. Put the assertion back and push again.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| Step 4: the build stops at `npm install` or `vite build` | The same error `npm run build` gives in `web/`. Usually an import whose capital letters do not match the file name: your laptop does not care, Linux does. |
| Step 4: the build stops at `composer install` with `lock file is not up to date` | `api/composer.lock` is not committed or is stale. Run `docker compose exec app composer update --lock`, then commit the lock file. |
| Step 4: the `app` container exits, and its log ends in a migration error | A migration that MySQL accepted and PostgreSQL does not. Read the SQL in the message. The usual ones: a raw `DB::statement` written for MySQL, or a column changed with `->change()`. Fix the migration, then `docker compose -f docker-compose.prod.yml down -v` and `up --build`. |
| Step 4: it starts but there is no data | Correct. Production runs `migrate`, never the seeder. Register and add a row. |
| Deploy check 1 fails with `000` | Nothing is listening. `docker compose -f docker-compose.prod.yml logs app` shows why it stopped. |
| Deploy check 2: 200, but JSON instead of a page | `web/dist` did not reach `public/`. Your React folder is not called `web/`, or `vite build` writes somewhere other than `dist`. Change the folder names in the `Dockerfile`'s `COPY` lines. |
| Deploy check 4 fails, check 2 passes | The fallback route is missing, or a `Route::get('/')` is still above it in `web.php`. |
| Deploy check 5 gets 500 | See the error once: add `APP_DEBUG: "true"` under `environment:` in `docker-compose.prod.yml`, `up` again, read the message, then take the line out. Usually a query written for MySQL. |
| Deploy check 6 says APP_DEBUG is true | The line from the row above is still there. Take it out. |
| Deploy check 7 gets 419, or check 8 gets 401 | Run the check with exactly the address in `SANCTUM_STATEFUL_DOMAINS`: `http://localhost:8090`, not `127.0.0.1`. |
| Deploy check 7 gets 500 `relation "sessions" does not exist` | Your migrations have no `sessions` table. Add one: `docker compose exec app php artisan make:session-table`, then commit the new migration. |
| Deploy check 9 fails: a cookie is not Secure | Your `api/config/session.php` sets `'secure'` to something other than `env('SESSION_SECURE_COOKIE')`: put that back. If it already says that, tell the instructor. |
| Deploy check 10 fails on your very first run | The script remembers the account it made at each address, in a file named after the address. Another app answered at the same address before (for example the pantry example on `localhost:8090`). Delete that file and run the check again: `rm "${TMPDIR:-/tmp}"/check-deploy-http___localhost_8090.txt`. |
| Deploy check 10 fails after you ran `down -v` | Expected: `-v` threw the database away, so last run's account is gone. The failing run made a new account, so the next run passes check 10. |
| Deploy check 11 fails: `no such header` | Your Homework 8 middleware is missing or not switched on. See [Homework 8, section 1](../hw8/README.md#1-security-headers--drill-6): `api/app/Http/Middleware/SecurityHeaders.php`, and `$middleware->append(...)` in `bootstrap/app.php`. |
| A page is blank in the production image, but not under `docker compose watch` | Your `Content-Security-Policy` from Homework 8 blocked something. The browser console names it: usually a file your page loads from another site. Add that origin to the policy, or serve the file yourself. |
| Step 8: `php` is red | A test fails on a clean machine. The job runs `php artisan test`; `docker compose exec app php artisan test` runs the same tests on yours. Fix the test or the code; do not delete the job. |
| Step 8: the login step fails with `AADSTS700213` or `No matching federated identity record` | The workflow is not running from `main`, or the job lost its `environment: lab` line. Azure trusts exactly that pair. |
| Step 8: the smoke check fails | The job's last step printed what your container said. A migration error means the PostgreSQL row above. Your address still shows the previous version: a revision that never answers `/up` never gets traffic. |
| Step 9: the first request takes half a minute | The app had scaled to zero. The check waits for it. |
| Step 11: *Repository and branch exist* fails | Check the repository address and the branch on your submission. If both are right, the repository is private and the hub cannot read it: tell the instructor. |
| Step 11: a hub check is red although you fixed it | The hub reads `main` on GitHub, not your laptop. Push, then press **Check again**. |
