# Teaching the week 13 deploy drills

[Student instructions](README.md) · [Worked answers](answers/README.md)

Tuesday 11/17 (session 23), with the deployment lecture. Four drills (about 20 minutes) and the
production image (about 15), on the to-do app from weeks 11 and 12; drills 5 and 6 are for
anyone who finishes. **Not graded:** the push to their own repository is participation. There
are no Check result buttons and no tests in the student copy (removed 11/16); each drill's
"Done when" names what they see. The tests are in
`itws2110-instructor/solutions/wk13` (a solved copy on port 9030; `bash check.sh starter`). Homework 10 (team, due Tue 12/8) makes changes 2, 3 and
4 again on the team projects and deploys them, so this is the first of two cases of the same
ideas. Friday 11/20 is team work time on Homework 10.

## Where each drill falls in the lecture

| After | Drills | Minutes | Pause point |
|---|---|---|---|
| Part 3's opener, before "Four places a web app can run" | the [bucket demo](../bucket/README.md) | 5 | students open the address; take prediction 3 before showing `/api/items` |
| Part 1, a copy that strangers use (settings, debug) | 1, 2 | 8 | everyone has two PASS before Part 2 |
| Part 3, migrations that keep the data | 3 | 6 | ask who still has "Deploy on Friday" |
| Part 3, the TLS slides | 4 | 6 | run the curl line on the projector, before and after |
| Part 2's local run, or the end of class | the production image | 15 | take the prediction (which fixes will the image have?) before anyone builds |
| if they finish | 5 (a new column), 6 (the pipeline) | 12 | optional |

The production image needs FrankenPHP and PostgreSQL downloaded. The week page asks students to
build it before class (`docker compose -f docker-compose.prod.yml build`); anyone who did not
will wait a few minutes, so start that step while you lecture on Part 2.

## Before class

- The bucket demo: `project-platform/infra/azure/lab/bucket.sh <path to project/pantry-example-midterm/web>`
  builds, uploads and prints the address (41 seconds on 11/14). Run it live, or beforehand and
  only show the three `curl` lines. `bucket.sh --empty` takes the files down.

- `docker compose watch` in `inclass/wk13/laravel`. **Port 8030**, compose project
  `itws2110-wk13`, so it runs beside weeks 11 (8000) and 12 (8020).
- Instructor check, outside the student copy: `bash itws2110-instructor/solutions/wk13/check.sh starter`
  fails **9 of 11 tests, in all 6 drills**; `check.sh answers`: **11 passed**.
- The production image: `docker compose -f docker-compose.prod.yml up --build`, **port 8035**,
  compose project `itws2110-wk13-prod`, PostgreSQL. Checked 11/16: from the starter, `/up` is
  404 (the health check never turns healthy), `/boom` shows the debug page and `/request` says
  http; with the answers, `/up` 200, `/boom` a bare 500, `/request` https, and a registered
  account and its to-do survive a restart. The image is built from the student's files, so it
  shows exactly which drills they did.
- Walked end to end in Docker on 11/14: each "see it break", each fix.

## The drills, and the one idea under each

| Drill | See it break | Change | The idea |
|---|---|---|---|
| 1 | `/boom` shows the debug page although `printenv APP_DEBUG` says `false` | `env('APP_DEBUG', false)` in `config/app.php` | settings live in the environment; default to safe (12-factor III) |
| 2 | `/up` is 404 | `health: '/up'` | the platform asks before it sends visitors (Homework 10's check 1) |
| 3 | add a to-do, `docker compose restart app`, gone | `migrate --force` in `start.sh` | a start is not a reset; rows live outside the container |
| 4 | the curl line with `X-Forwarded-*` headers still reports http | `trustProxies(at: '*')` | TLS ends at the edge; the app must be told (Homework 10's checks 8 and 9) |
| image | `Dockerfile.prod` built from their files, run on 8035 | nothing | one image, settings from outside, FrankenPHP in place of `artisan serve`, data kept across a restart |
| 5 (optional) | Sort by priority: 500; the log says *Unknown column* | a new migration with `Schema::table` and a default | the database moves forward one migration at a time; read the log |
| 6 (optional) | prediction: a red test still deploys | `needs: [test]` | the gate is one line |

## Things worth knowing

- **`start.sh` serves with `--no-reload`.** Without it `artisan serve` strips the container's
  environment and reads `.env` only, so `APP_DEBUG=false` would never reach the app and drill
  1 could not be seen in the browser.
- **Drill 5 has students create the file.** A supplied empty migration would be recorded as
  run at the first start, and filling it in later would do nothing. A student who saves a
  half-written file hits the same thing: the troubleshooting table has the rollback.
- **Drill 5 depends on drill 3.** With `migrate:fresh` still in `start.sh` the next restart
  drops "Deploy on Friday", and the "rows are kept" point is lost.
- **After drill 3 a `down -v` leaves an empty database**, as in production. `db:seed --force`
  brings the three accounts back.
- **Drill 4's curl runs inside the container** so it works the same in every shell; that is
  why the host reads `localhost:8000`, not `8030`.
- HSTS is not a drill here: week 11's `SecurityHeaders` already sends it, and Homework 10's
  check 11 is where it finally means something.
