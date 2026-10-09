# Week 7 — October 6 and October 9

[← Course home](../README.md)

| | |
|---|---|
| **Tue 10/6** | Laravel I: what a framework buys you. The patterns behind every web framework — a front controller, a routing table, MVC, templates that escape by default, request objects handed to your code — each one a job week 3's `index.php` did by hand. Then Laravel's version of each. Drills 1–5 of the [week 7 workshop](../inclass/wk7/laravel/) for the last part of class. **In-class: push your copy of the workshop to your repo at the end of class**, with whatever you finished ([how](../inclass/wk7/README.md)). |
| **Fri 10/9** | Laravel II: controllers, requests, and HTTP tests — drills 6–9 of the same workshop, then reading the drill checks as tests. *Quiz 1 review.* **Homework 5 due. [Homework 6](../homework/hw6/README.md) assigned** — the to-do list in Laravel, due Fri 10/23. |

Both days use one new folder, [`inclass/wk7/laravel/`](../inclass/wk7/laravel/). It runs in
Docker like week 3 — no PHP or Composer to install. Build it **before Tuesday**; the first run
downloads PHP and Laravel's packages and takes a few minutes:

```bash
cd inclass/wk7/laravel
docker compose watch
```

When it says `Watch enabled`, open http://localhost:8000.

## The nine drills

Each one is something week 3's `index.php` did by hand. Same **Check result** button as the
React drills — here it runs a Laravel test against your files.

- **Routes and views** — 1. a route is one line · 2. route parameters · 3. return a view ·
  4. escaping is the default · 5. a layout
- **Controllers and requests** — 6. a controller · 7. named routes · 8. a real 404 ·
  9. the framework hands you the request

---

## Reading

The question for the week: **a framework calls your code, instead of you calling it. What
did you trade for that?** In week 3 your `index.php` was in charge of every request. From
Tuesday, Laravel is, and your code waits to be called.

Nothing is required this week. The book is optional, and the docs are there for when a drill
sends you looking.

### 1. Matt Stauffer, *Laravel: Up & Running*, 3rd ed. — chapters 1, 3, 4 and part of 12 (

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/laravel-up/9781098153250/)
— sign in with your RPI credentials when prompted, the same way as the CSS in Depth book.

The docs tell you what each function does. This book, by a long-time Laravel teacher, tells
you how the pieces fit, which is what makes the docs readable. It covers Laravel up to version 11 and we use
13: the ideas below haven't changed, but version numbers, install steps and some details
have. Use it for the mental model and the docs for specifics.

| Chapter | | |
|---|---|---|
| 1 | Why Laravel? | read *Why Use a Framework?* and *"I'll Just Build It Myself"*. The week's question, answered from the framework's side. |
| 3 | Routing and Controllers | alongside the drills: MVC, route parameters and names, views, controllers, *Injecting Dependencies into Controllers* (drill 9) and *Aborting the Request* (drill 8). |
| 4 | Blade Templating | alongside drills 3–5: echoing data, loops, and *Using Components* for the layout. |
| 12 | Testing | before Friday: the *HTTP Tests* section only. It is what the drill checks are. |

Skip chapter 2. It installs Laravel with tools we don't use — our Docker setup replaces all of
them.

### 2. Laravel docs — reference, for the drills

Not a reading assignment: open these when a drill sends you looking.

- [Request lifecycle](https://laravel.com/docs/13.x/lifecycle) — what happens between `public/index.php` and your code. Short; worth reading once in full.
- [Directory structure](https://laravel.com/docs/13.x/structure) — where things go, and why.
- [Routing](https://laravel.com/docs/13.x/routing) · [Blade templates](https://laravel.com/docs/13.x/blade) · [Controllers](https://laravel.com/docs/13.x/controllers) · [Requests](https://laravel.com/docs/13.x/requests)
- [Service container](https://laravel.com/docs/13.x/container) — the machinery behind drill 9.
- [HTTP tests](https://laravel.com/docs/13.x/http-tests) — for Friday: what the drill checks are, and how to write your own.

We use Laravel 13. Tutorials and books written for older versions are mostly right about the
ideas and often wrong about the details; when they disagree with the docs, the docs win.

---

## Due this week

| | Due |
|---|---|
| **In-class** — your copy of `inclass/wk7/laravel/`, pushed with whatever you finished | **Tue 10/6, end of class** |
| **Homework 5** — a to-do list, and two tests of your own | **Fri 10/9, 11:59 PM** |

**Quiz 1 is next Tuesday, 10/13** — handwritten and closed-device. It covers sessions 1–11,
through component testing. Laravel is not on it. Friday's second half is review.

I'm working on the sample questions. They will be posted soon. 