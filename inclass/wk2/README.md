# Week 2 — Docker

| | | Due |
|---|---|---|
| **Tue 9/1** | One service at a time — a database, then a web server, never both | `ANSWERS-tue.md` — 3 questions, tonight 11:59 PM |
| **Fri 9/4** | Make them talk — networks, then Compose | `ANSWERS-fri.md` — 3 questions, tonight 11:59 PM |

Both count toward participation. **Homework 1 is assigned Friday.**

## Get your answer files

Copy both files from [`starter/`](starter/) into `inclass/wk2/` in **your own private
repository**:

```bash
cp -R ../itws2110-course/inclass/wk2/starter/. inclass/wk2/
```

Each is **three questions**, answered in your own words. They are not asking what you
typed — they are asking what the exercises were demonstrating. Skim them before you
start, so you know what to pay attention to.

---

## Tuesday — one service at a time

You run a database in a container, then a web server in a container. **They will not talk to each other.** That is deliberate: every failure on Tuesday has exactly one container to blame.

| | | |
|---|---|---|
| 1 | [MySQL, no Dockerfile](lamp-docker/example-1/) | run it, get a shell inside it, lose the data, keep the data |
| 2 | [MySQL, custom image](lamp-docker/example-2/) | put the schema in the image |
| 3 | [Apache and the filesystem](lamp-docker/example-3/) | serve files from your laptop |
| 4 | [PHP writing to disk](lamp-docker/example-4/) | where a container's writes go, and how long they last |

## Friday — make them talk

| | | |
|---|---|---|
| 5 | [Two containers, no Compose](lamp-docker/example-5/) | a network, DNS by container name, a startup race |
| 6 | [The same thing, declared once](lamp-docker/example-6/) | Compose, healthchecks, `.env` |
| — | [Compose is not a PHP tool](lamp-docker/example-mern/) | the identical file, against Node and MongoDB |
| 7 | [Production-shaped](lamp-docker/example-7/) | *optional* — least privilege, migrations, non-root |

Then read [Homework 1](../../homework/hw1/README.md) before you leave. It is due Friday 9/11 and it is not a one-evening assignment.

---

## Submit

Commit to `main` and push. No branch, no pull request.

```bash
git add inclass/wk2/
git commit -m "In-class week 2"
git push
```

Answers go in `inclass/wk2/` **in your repository**. Do not edit files in the course repository — you cannot push to it, and the next `git pull` would discard your changes.
