# Week 2 — Docker, one tier at a time

Instructor runbook. The week is built so that **Tuesday runs one service at a time and Friday connects them.** Nothing talks to a database until Friday, which keeps every Tuesday failure a single-container failure with a single-container cause.

The LAMP stack itself is not demonstrated here. It is **[Homework 1](../../../homework/hw1/README.md)** — students assemble it themselves from these parts.

## Tuesday 9/1 — one service at a time

| | Example | Idea | Ends with |
|---|---|---|---|
| 1 | [`example-1/`](example-1/) | `docker run`, `exec` into a shell, ports, volumes | Schema typed by hand; nothing reproducible |
| 2 | [`example-2/`](example-2/) | `docker build`, images, layers, `init.sql` | An image that ships its own schema |
| 3 | [`example-3/`](example-3/) | **Apache**; bind mounts, `:ro`, mount-over, host↔container files | Files served from your laptop |
| 4 | [`example-4/`](example-4/) | **PHP writing to disk**; restart vs. `rm`, volume ownership | Two isolated machines that cannot see each other |

Examples 3 and 4 have no database in them at all. That is deliberate. By the end of Tuesday students have a web tier and a data tier, and the honest observation that **they have not spoken.**

## Friday 9/4 — make them talk

| | Example | Idea | Ends with |
|---|---|---|---|
| 5 | [`example-5/`](example-5/) | user-defined network, DNS by container name, the startup race | Six commands in an exact order |
| 6 | [`example-6/`](example-6/) | Compose, **`compose watch`**, healthchecks, **`.env` credentials** — **against Node + Mongo, not LAMP** | One file, one command |

Example 6 is deliberately not PHP. Students never see a finished LAMP `docker-compose.yml`
in class — **assembling one is Homework 1**, assigned at the end of Friday. What they get
instead is the identical set of ideas (service-name DNS, healthchecks, volumes, `watch`)
on a stack where copying is impossible.

Two former examples — LAMP-on-Compose and the production-hardening one — are retired to
`itws2110-instructor/notes/retired-examples/` for reuse in the security and ORM weeks.

## The four moments worth protecting

- **1D** — destroy the container, lose the data, then do it again with a volume.
- **2C** — change `init.sql`, rebuild, watch nothing happen. The most common self-inflicted bug in this course.
- **4B** — `docker restart` keeps the guestbook, `docker rm` + `docker run` does not. Same rule as 1D, now in the web tier. If they get this, they understand containers.
- **5E** — the container is *running* but the database is not *ready*. 6B shows the fix.
- **6D** — change the password in `.env` without `down -v`: `Authentication failed`. The answer to 5's closing question, and Example 2C wearing a different hat.
- **6C** — `docker compose watch`, then edit `server.js` and watch the terminal say `Syncing service "server"` and restart it. From here on this is how they run everything.

Each README ends with questions that have no copy-pasteable answer. Those are the ones worth asking out loud.

## Before class

```
docker pull mysql:8.4
docker pull httpd:2.4
docker pull php:8.3-apache
docker pull mongo:8
docker pull node:18-alpine
```

Five images over lecture-hall wifi at once will not go well. Put this on the week-1 slide, or budget ten minutes.

## Port conflicts

Examples 1-5 publish 8080; example 6 publishes 3000. That is deliberate — students collide with themselves and have to read the error. Tear down before moving on:

```
docker rm -f web1 web2 gb db1 db2 web db     # examples 1-5
docker network rm itwsnet                    # example 5
docker compose down -v                       # example 6
```

Example 6 is run with **`docker compose watch`**, which stays in the
foreground and syncs edits into the running containers. `Ctrl-C` stops watching; the
containers keep running until `docker compose down`. Requires Compose v2.22 or newer —
check with `docker compose version`.

## Housekeeping

- All PHP/MySQL examples run `mysql:8.4` and `php:8.3-apache`, matching the student template.
- `example-6/` has a `.gitignore` for `node_modules/`. Keep it — without it, a student's `npm install` recommits 15 MB.
- `LAB.md.bak` is the retired week-1 lab. Its Part C survives as Homework 1, Task 6.
- This folder is still named `lamp-docker`, which no longer describes it — LAMP moved to Homework 1.
