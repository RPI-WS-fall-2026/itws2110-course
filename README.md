# ITWS 2110 — Web Systems Development
### Fall 2026 · Rensselaer Polytechnic Institute

**Instructor:** Jason Kuruzovich · kuruzj@rpi.edu · Pitt 2206
**Office hours:** Tuesday 9:00–11:00 AM, or [book a time](https://bit.ly/jason-rpi)

---

> ### Start here → **[Week 1](weeks/week-01.md)**
> Take the class survey, install GitHub Desktop and Docker, and start the Week 2 reading.

---

## How this course works

**Tuesday** is lecture. A reading is due, and there is an in-class exercise — due that evening unless the week's page says otherwise. These count toward participation.

**Friday** is lab. You work on that week's homework, which is due the following Friday at 11:59 PM.

Assignments are graded by automated tests. You run them yourself before submitting, so you know your score in advance.

**Docker, all semester, three commands.** Every app in this course ships with a `docker-compose.yml`, and you drive it the same way every time:

| You want to | You type |
|---|---|
| Bring the app up and work on it | `docker compose watch` — starts it, then syncs your edits in as you save |
| Run a one-shot job: tests, a migration, a shell | `docker compose run --rm <service>` |
| Stop it / wipe its data and start over | `docker compose down` / `docker compose down -v` |

(`Ctrl+C` in the watch terminal stops the watching, not the app. `down` stops the app.)

---

## In this repo

| | |
|---|---|
| [weeks/](weeks/) | One page per week — readings and what is due |
| [slides/](slides/) | Lecture slides, posted after each session |
| [syllabus/](syllabus/) | Course syllabus and full calendar |
| `homework/` | Assignments, released as we go |
| `inclass/` | Tuesday in-class labs |
| [project/](project/) | The group project — scope, timeline, templates |

---

## Full schedule (Tentitive)

Weeks are linked as they are posted. Everything is due at **11:59 PM** on the date shown.

| Week | Sessions | Topic | Due |
|---|---|---|---|
| **[1](weeks/week-01.md)** | Fri 8/28 | Course introduction; the modern web application | Survey — Fri 8/28 |
| **[2](weeks/week-02.md)** | Tue 9/1 · Fri 9/4 | Docker and infrastructure as code; LAMP on Docker | *Nothing due* |
| **[3](weeks/week-03.md)** | Tue 9/8 · Fri 9/11 | Test-driven development; writing tests at three levels | In-class — Tue 9/8<br>HW 1 — Fri 9/11 |
| **[4](weeks/week-04.md)** | Tue 9/15 · Fri 9/18 | CSS frameworks | In-class — Tue 9/15<br>HW 2 — Fri 9/18 |
| **[5](weeks/week-05.md)** | Tue 9/22 · Fri 9/25 | React — components and the render model | In-class — Tue 9/22<br>HW 3 — Fri 9/25<br>**Project pitch — Fri 9/25** |
| **[6](weeks/week-06.md)** | Tue 9/29 · Fri 10/2 | React — state, composition, component testing | In-class — Tue 9/29<br>HW 4 — Fri 10/2 |
| **[7](weeks/week-07.md)** | Tue 10/6 · Fri 10/9 | Laravel — what a framework buys you | In-class — Tue 10/6<br>HW 5 — Fri 10/9 |
| 8 | Tue 10/13 | **Quiz 1** — handwritten, closed-device | Quiz 1 — Tue 10/13 |
| — | *Fri 10/16* | *No class — follows a Monday schedule* | — |
| **[9](weeks/week-09.md)** | Tue 10/20 · Fri 10/23 | Eloquent ORM, migrations, relationships | In-class — Tue 10/20<br>HW 6 — Fri 10/23 |
| **[10](weeks/week-10.md)** | Tue 10/27 · Fri 10/30 | Forms, validation, sessions, authentication | In-class — Tue 10/27<br>**Project midterm update — Fri 10/30** |
| 11 | Tue 11/3 · Fri 11/6 | Security — OWASP Top 10 | In-class — Tue 11/3<br>HW 7 — Fri 11/6 |
| 12 | Tue 11/10 · Fri 11/13 | APIs, REST, and contracts | In-class — Tue 11/10<br>HW 8 — Fri 11/13 |
| 13 | Tue 11/17 · Fri 11/20 | Cloud deployment | In-class — Tue 11/17<br>HW 9 — Fri 11/20 |
| — | *11/23–27* | *Thanksgiving Break* | — |
| 14 | Tue 12/1 · Fri 12/4 | Performance; **Quiz 2** | In-class — Tue 12/1<br>Quiz 2 — Fri 12/4 |
| 15 | Tue 12/8 · Fri 12/11 | Final presentations | HW 10 — Tue 12/8<br>**Project presentation — Fri 12/11** |
| — | *12/16–22* | *Exam period* | **Project report — Wed 12/16** |

**HW 6 and HW 10 get longer windows** because the calendar removes a Friday — 10/16 follows a Monday schedule, and Thanksgiving falls between 11/20 and 12/4.

The [syllabus](syllabus/) is tentitive for dates but authoritative for grading and policies.
