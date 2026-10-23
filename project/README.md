# Group project — Fall 2026

**25% of the grade. Teams of 4–5, assigned by section. One repository per team.**

Build one small, real web application for people connected to this program: **current
students, future students, or alumni.** It has to be something a real person in one of
those groups would open twice.

## The minimum

Last time this project had too many deliverables. This time the floor is fixed and small,
and it is the same for every team. Reaching it is a B. What you build on top of it, and how
well you can explain it, is the rest of the grade.

Every project has at least these parts, and they are the things the homeworks teach, in the
order they teach them:

The project must include user login and user management appropriate to the application,
including account registration, login and logout, profile management, and role-based
permissions as applicable to its users and workflows.

| Part | What it means | You learn it in |
|---|---|---|
| **User management** | Register, log in, log out. Two roles: an ordinary user and an admin. A user can edit their own profile; an admin can see all users. | HW 7–8 (Laravel auth) |
| **One core resource** | The thing your app is about — a listing, a review, a post, a booking, a question. One database table, plus the relationships it needs (it belongs to a user, at least). | HW 6–7 (Eloquent) |
| **A JSON API for that resource** | Five endpoints: list, show, create, update, delete. Create/update/delete require login; delete and editing someone else's require admin. Documented. | HW 9–10 (APIs) |
| **A React front end** that uses the API | The pages a person needs: browse, see one, create, edit their own, log in. Responsive. | HW 3–5, HW 10 |
| **Runs in Docker** | One `docker-compose.yml` at the root of the repo. `docker compose watch` brings up the API, the front end and the database on a fresh clone with nothing installed but Docker. The database seeds itself. | HW 1 |
| **Tests at three levels** | PHPUnit unit tests on the rules · Playwright API tests on all five endpoints, including the error cases · Playwright browser tests on the two most important pages. Each suite is a `docker compose run --rm` service, like Pantry and Grocy. | HW 2, HW 5 |
| **Deployed** | Running at a public URL, from `main`, with a deploy your team can repeat. | HW 10–11 |

That is the minimum. It should be done, tested and deployed by the week-13 lab (11/20).

## Beyond the minimum

What separates an A from a B is depth on top of that floor, chosen deliberately and
written up in the report. Pick one or two, not five:

| Direction | What it looks like |
|---|---|
| **A second resource with a real relationship** | Comments on the core resource, tags, a favourites list. Now the API has a nested route and the front end has a second form. |
| **Search and filtering that a person would use** | Query parameters on the list endpoint, indexed, with the UI to match. |
| **Accessibility, measured** | Keyboard-only walkthrough, screen-reader labels, contrast checked, and a paragraph on what you found. |
| **Performance, measured** | An N+1 you found and fixed, a response time before and after, a Lighthouse score you moved. |
| **Security beyond the defaults** | Rate limiting, an audit of your own OWASP top-ten exposure, a finding you fixed. |
| **Test depth** | Coverage of the rules, contract tests for every error case, a browser test for every page. |
| **A real user** | Three people from your audience used it. What they did, what confused them, what you changed. |


Students must use the technologies covered in detail in the labs and homework,
including **Tailwind CSS for styling, React for the front end, and Laravel for the API**.
The course stack also includes MySQL, Docker Compose, PHPUnit, and Playwright, deployed
to the cloud provider we use in week 13. You may not substitute.

## Docker and tests are not optional parts

Two things run through every deliverable and are checked at every one of them:

**Docker.** From the first commit, the project runs the way everything in this course has
run: `docker compose watch` for the app, `docker compose run --rm <suite>` for the tests,
`docker compose down`. A grader clones the repo, copies `.env.example` to `.env`, runs one
command, and has the whole system. "It works on my laptop" is not a state the project can
be in, because nobody installs PHP, Node or MySQL on a laptop for it. At the pitch this
means a compose file that boots a placeholder API and front end; at the midterm it means the front end serves from
a container; at the end it means the same file runs the deployed system.

**Tests.** The three levels from week 3 are the three levels here, and each has its own
compose service so it runs with one command:

| Suite | Tool | What must be covered | First expected |
|---|---|---|---|
| `unit` | PHPUnit | The rules: validation, who may do what, any arithmetic or date logic. No database. | Midterm: the fake-store rules |
| `api` | Playwright `request` | All five endpoints, the success case and every error case (`401`, `403`, `404`, `422`). | Week 12, as the API lands |
| `e2e` | Playwright | The two most important pages as a person uses them, including log in. Own data per test. | Midterm: one on the browse page |

A feature without a test at the right level is not done. The presentation runs all three
suites live, and the paper reports what each covers and one bug a test caught first.

## Who it is for

Pick one audience and one problem they actually have. Some starting points, none of them
required:

- **Current students:** where to find a study room right now; which upperclassman took this
  elective and what they thought; a lost-and-found for the building; a ride board for
  breaks.
- **Future students:** questions admitted students ask and answers from people who were
  just admitted; a "what my first semester actually looked like" board.
- **Alumni:** who is at which company and open to a coffee chat; a place to post an
  internship lead for one specific program rather than all of LinkedIn.

The test of an idea: can you name the one core resource in a sentence? *"A study-room
report: which room, how full, when."* *"A course review: which course, which semester, a
rating, a paragraph."* If it takes two sentences, it is two resources.

## Timeline

Four deliverables, each on a day the course is already doing something related.

| When | Deliverable | Format | Weight |
|---|---|---|---|
| **Fri 9/25** (week 5) | **Pitch** | 5 minutes in lab, and `PITCH.md` in the team repo | 15% of project |
| **Fri 10/30** (week 10) | **Midterm: front-end prototype + API design** | 8 minutes in lab: the React prototype running against fake data, and the API design reviewed on screen | 20% |
| **Fri 12/11** (week 15) | **Final presentation** | 12 minutes: the deployed app, live; the tests, run live; what you built beyond the minimum; one thing you would reverse | 30% |
| **Wed 12/16** (exam period) | **Final report** | `REPORT.md`, a six-to-eight page paper: the system, the decisions, the evidence, the process | 35% |

Between deliverables the work is the homework: HW 3–5 produce the front end, HW 6–8 the
back end and auth, HW 9–10 the API and the wiring, HW 11 the deployment. The project is
where those pieces are pointed at your idea instead of at the assignment's example. The
minimum should be in place by the week-13 lab; weeks 13–15 are for what goes beyond it
and for the paper.

### Fri 9/25 — Pitch

Five minutes, no slides required. Who it is for, what the core resource is, what the five
API endpoints do, what the two most important pages show. Then the team repo is created from
the template and `PITCH.md` is filled in and committed by that evening. A pitch that
names two resources or an integration from the "not on the list" list goes back for a
rewrite by the following Tuesday.

### Fri 10/30 — Midterm: front-end prototype and API design

Two things, graded separately, 10% of the project each. No back end is expected — the API
is not taught until week 12. That is the point: the front end and the contract are done
first, so the Laravel API has something exact to implement.

**1. The front-end prototype.** A React app in `web/` that runs from `docker compose watch`
and works against **fake data** — a JSON file or an in-memory array, shaped exactly like the API
responses in `API.md`. It has the pages a person needs:

| Page | What it must do against fake data |
|---|---|
| Browse | List the core resource. Responsive: usable at 375 px and at 1280 px. |
| Detail | One item, with its owner and dates. |
| Create / edit | A form with validation messages, for a logged-in user. Submits to the fake store and the list updates. |
| Log in / register | The forms, and a visible logged-in state (name in the header, logout). Fake for now. |
| Admin | One thing an admin sees that a user does not — even if it is only a "delete" button that appears. |

A component test on the form (HW 5 taught it) and one Playwright browser test on the
browse page (HW 2 taught it) run green from `docker compose run --rm e2e`. Swapping the fake store for real `fetch` calls in week 12 should
be a one-file change; if it is not, the prototype was not written against the contract.

Every page that loads data needs a **loading state and an error state**, and the two key pages need a way to move
between them. Neither is in the homeworks yet; the **[midterm appendix](MIDTERM_APPENDIX.md)** has a working example
of each — a fake-data module, a page with loading, error and empty states, a form that shows `422` messages, and
how to make a load fail on purpose for the lab demo.

**2. The API design.** `API.md` filled in: every field of the resource with its type and
validation rules, the five endpoints with request and response bodies, which need login and
which need admin, the error shapes, and one request/response pair written out in full with
the fake data the prototype shows. We review it on screen in lab, and I will ask about the
choices — why that field is required, why that endpoint is admin-only, what happens when
the owner deletes their account.

**In lab:** eight minutes per team. Four on the prototype (click through the five pages),
four on the API design. `PROCESS.md` has its first five weekly entries.

### Fri 12/11 — Final presentation

Twelve minutes, every member speaks, questions go to individuals. Show, in this order:

1. The deployed app at its public URL. A user registers, creates the core resource, an
   admin does something the user cannot. Two minutes; this is the minimum and it should
   be boring by now. Then, from a clean clone on the podium machine, `docker compose
   watch` — the same system comes up locally.
2. What you built beyond the minimum, and the evidence that it works or that it mattered —
   a number, a test, a user's reaction. Five minutes; this is the part we came for.
3. The three test suites, run live from their compose services, green. Say what each
   one covers.
4. One decision you would reverse, and why.

Tag the release `v1.0` before you present; that tag is what is graded.

### Exam period — Final report

`REPORT.md`, a paper of six to eight pages, in the repo by Wed 12/16, five days after the presentation. The template is in the
team repo and its sections are fixed. It is the record of the system and of the team, and
it is where the "beyond the minimum" work is argued for with evidence. Roughly:

1. **The problem and the audience** — who, what they were trying to do, how you know.
2. **The system** — an architecture diagram, the data model, the API, how the pieces are
   deployed. A reader with this section and the repo should be able to run it.
3. **Decisions** — three or four you made on purpose, each with the alternative you did not
   take and why. The API design questions from the midterm belong here.
4. **Beyond the minimum** — what you chose, why that, and the evidence: measurements,
   test output, what a user did.
5. **Testing** — the three suites: what each covers, what it does not and why, how long it
   takes, and one bug a test caught before a person did.
6. **Running it** — the one command for the system, the one for the tests, and what a
   grader sees on a clean clone.
7. **Security and deployment** — what you checked, what you found, how a deploy happens.
8. **Process** — how the team worked, from `PROCESS.md`: what each person did, what broke,
   what you would do differently.
9. **Individual statements** and **advice to next year's teams**.

The application is graded from the tag; the paper is graded on whether a stranger could
understand and trust the system from it, and on honesty.

## How the team works

- **One repo, `main` is the product.** Work on branches, merge by pull request, at least
  one review per merge. This is the one place in the course where pull requests are
  required, because it is the one place with more than one author.
- **`PROCESS.md` is a weekly log**, one short entry per week from the pitch on: what was
  done, by whom, what is blocked. Ten lines. The report is written from it.
- **Everyone commits.** Git history is part of the grade for each individual. A member with
  no commits after week 10 is graded separately from the team.
- **Roles rotate or they do not — your call — but write it down.** Who runs the weekly
  check-in, who owns the deploy, who owns the tests.

## Grading

The project is 25% of the course grade, split as in the timeline table. Within each
deliverable, the team gets one grade, adjusted per person by the contribution evidence:
commits, PR reviews, the `PROCESS.md` log, and who answered what in the presentation.
Adjustments are rarely more than a letter grade in either direction, and never a surprise:
if your contribution is thin at the midterm update, you will hear it then.

## The team repository

Team repos are created from
[itws-2110-project-template](https://github.com/RPI-WS-fall-2026/itws-2110-project-template)
after the pitch. It holds the four documents the timeline asks for, each a template with
the questions already in it:

| | |
|---|---|
| `PITCH.md` | The 9/25 pitch, written down |
| `API.md` | The API contract, due with the midterm update |
| `PROCESS.md` | The weekly log. Start it the day of the pitch |
| `REPORT.md` | The final report |

The application goes in the same repo, in the layout the homeworks establish.
