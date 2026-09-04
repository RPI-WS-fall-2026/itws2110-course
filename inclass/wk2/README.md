# Week 2 — Docker

| | |
|---|---|
| **Tue 9/1** | One service at a time — a database, then a web server, never both |
| **Fri 9/4** | Make them talk — networks, then Compose |

**One hand-in for the week: `ANSWERS.md`, three questions on Tuesday's exercises, due
Friday 9/4 at 11:59 PM.** It counts toward participation. Friday's session has nothing of
its own to submit.

**Homework 1 is assigned Friday.**

## Get the answer sheet

Copy [`starter/ANSWERS.md`](starter/) into `inclass/wk2/` in **your own private
repository**:

```bash
cp -R ../itws2110-course/inclass/wk2/starter/. inclass/wk2/
```

**Three questions**, answered in your own words. They are not asking what you typed —
they are asking what the exercises were demonstrating. Skim them before you start, so you
know what to pay attention to, and hand them in by Friday night.

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
| 6 | [The same thing, declared once](lamp-docker/example-6/) | Compose and `compose watch` — on Node + MongoDB, so the ideas are what you take away |

Then read [Homework 1](../../homework/hw1/README.md) before you leave. It is due Friday 9/11 and it is not a one-evening assignment.

---

## Submit

Commit to `main` and push. No branch, no pull request.

```bash
git add inclass/wk2/
git commit -m "In-class week 2"
git push
```

Your answers go in `inclass/wk2/ANSWERS.md` **in your repository**, by Friday night. Do not edit files in the course repository — you cannot push to it, and the next `git pull` would discard your changes.
