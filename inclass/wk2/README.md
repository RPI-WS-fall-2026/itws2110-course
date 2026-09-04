# Week 2 — Docker

| | |
|---|---|
| **Tue 9/1** | One service at a time — a database, then a web server, never both |
| **Fri 9/4** | Make them talk — networks, then Compose |

**Nothing is due for this week's in-class work. Homework 1 is assigned Friday.**

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
| 6 | [The same thing, declared once](lamp-docker/example-6/) | Compose, `compose watch`, and where the password lives — on Node + MongoDB, so the ideas are what you take away |

Then read [Homework 1](../../homework/hw1/README.md) before you leave. It is due Friday 9/11 and it is not a one-evening assignment.
