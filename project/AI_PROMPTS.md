# Using AI tools on the project

Claude Code, ChatGPT, Cursor, or whatever you use. This page is how we use them on the project,
and a sample prompt for each stage. The syllabus rule still applies: you are responsible for
every line you submit, and you may be asked to explain any of it in person.

## What to do

1. Put `AGENTS.md` (below) in the root of your team repo, make the `context/` folder it
   points at, and add `scratch/` to `.gitignore`.
2. Ask the tool to write new work into `scratch/<task>/` with a `NOTES.md`, never into `api/`
   or `web/`.
3. Move the result into the project yourself, file by file, reading every line as you go.
4. Run the suite that covers it (`docker compose run --rm unit`, `api`, or `e2e`) before you
   open the pull request. The PR is what you moved, not what the tool emitted.
5. Build the minimum for each stage, let a human look at it, then start the next stage.
6. Add one AI line to each week's `PROCESS.md` entry: what you used it for, one thing it got
   wrong and how you caught it.

## `AGENTS.md` for your team repo

Every tool reads a file in the repo root that says what the project is and how to work in
it. Codex and Cursor read `AGENTS.md`; Claude Code reads `CLAUDE.md`, which can be one line,
`@AGENTS.md`. Write it in your own voice: it is a note to a helpful colleague about who you
are and what you are trying to learn, what you are building with, how to hand work back to
you, and that it may help you troubleshoot in the main directory. Not a list of rules. Start
from this and change the names and the resource to yours.

```markdown
# <Team name> — <app name>

## Who you are working with
I am a student in a web development class (ITWS 2110), building this app with a
team of four. I want to fully understand the platform I am building, not just to
ship it: I will be asked to explain any line of this code in person, and the
quizzes test what I learned here. So teach as you go. Say what each piece does and
why, name the Laravel or React idea behind it, and give me outside links to the
documentation so I can read more. Prefer the simple version I can follow over the
clever one.

## What we are building with
- React, in web/ (Vite, with Vitest for component tests)
- Tailwind for styling
- Laravel, in api/ (Eloquent, Form Requests, policies, Sanctum for login)
- MySQL, and everything runs in Docker: docker compose watch for the app,
  docker compose run --rm unit | api | e2e for the tests

API.md at the root is the contract. context/ has the background: the course's
project description, the wireframe we chose, my notes on the readings, and how we
build things in this class. Read it before suggesting code.

## How to hand me new work
Put new features in scratch/<task>/, not in web/ or api/, with a NOTES.md that
explains each file and where it goes. For example:

    Build the "create listing" page. Put everything in scratch/create-listing/
    and write NOTES.md explaining each file and where it goes in web/.
    Do not edit web/.

    scratch/create-listing/
      NOTES.md                what each file is, where it moves, and why
      CreateListing.jsx       -> web/src/pages/
      CreateListing.test.jsx  -> web/src/pages/
      api.js.patch            two functions to add to web/src/api.js
      App.jsx.patch           the route to add

Then I, the student, move everything in by hand. That is how I learn it.

## Troubleshooting
You can also help me troubleshoot existing issues with my codebase, in the main
directory. Read the error and the code, explain what is going wrong and why, and
suggest the fix. When the fix is more than a line or two, write it to scratch/ as
above so I can carry it in myself.
```

### The `context/` folder

The file points the tool at `context/`, so make it. It holds the things the tool cannot see
in the code and would otherwise guess at:

| File | What to put in it |
|---|---|
| `context/PROJECT.md` | A copy of the course [project README](README.md) |
| `context/MIDTERM_APPENDIX.md` | A copy of the [midterm appendix](MIDTERM_APPENDIX.md) |
| `context/wireframe/` | The wireframe version you chose at stage 1, and a line on why |
| `context/readings.md` | Your notes on the week's readings, in your words. The tool will use your vocabulary back at you, which is how you find out whether you understood them. |
| `context/how-we-build.md` | A short page on how we build things in this class, in your words: Eloquent with bound parameters, validation in Form Requests, who-may-do-what in a policy on the server, config in `.env`. Practices you have learned, not rules. |

Add to it as the project goes: the API design review notes, what your audience said about
the prototype, the decision you argued about. The better the folder, the less the tool
invents, and the more of its answers come back in terms you already know.

## New work goes to `scratch/`, then you carry it in

A prompt for a feature does not write into `api/` or `web/`. It writes to `scratch/<task>/`,
with a `NOTES.md` that says what each file is, where it goes, and why. You carry it in by
hand. Moving is reading: that is how you end up able to explain the code, and it keeps the
tool from breaking the running app or your team's branch. Troubleshooting an existing
problem in the main directory is fine; a one-line fix the tool proposes in chat, you type
in yourself.

A `NOTES.md` that is good enough:

```markdown
# create-listing

CreateListing.jsx -> web/src/pages/CreateListing.jsx
  The form. Uses useSession() for the current user and api.createListing().
  Validation messages come from the 422 body, shown next to each field.
CreateListing.test.jsx -> web/src/pages/
  Three Vitest cases: renders, shows a 422 message, calls the API on submit.
api.js.patch -> apply to web/src/api.js
  Adds createListing(data) and updateListing(id, data), using request().
App.jsx.patch -> apply to web/src/App.jsx
  One route: /listings/new, wrapped in RequireLogin.
Not done: the edit page. The admin-only delete is a separate task.
```

Delete the scratch folder when the move is done.

## The stages: build the minimum, then a human looks

Build in this order, one small stage at a time, with a review before the next one starts. A
wrong assumption caught at the wireframe costs one prompt. Caught after the backend, it costs
a rewrite of three layers. Ask for several versions early and one of everything late:
alternatives are what the tool is best at, and choosing is what the team is for.

| Stage | The tool generates | A human reviews | Done when |
|---|---|---|---|
| 1. Wireframes | Three versions of your five pages | The team picks one; two people from your audience react to it | "Would a person open this twice?" |
| 2. React on fake data | One page at a time, the midterm shape | Teammates click through every page against the midterm appendix | The five pages work on fake data |
| 3. `API.md` | Options: field types, validation rules, error shapes | You write the contract and can defend every line | The API design review in lab |
| 4. Backend | One endpoint at a time, against its API test | PR review, plus the three suites | `docker compose run --rm api` is green |
| 5. Beyond the minimum | Only after the floor is deployed | The report's evidence: a number, a test, a user | The grader can run it |

### Stage 1: wireframes, three versions

```
Here is our PITCH.md. We are building <app> for <audience>. Make three different
wireframes of the five pages (browse, see one, create, edit, log in) as single
static HTML files with Tailwind, no JavaScript, in scratch/wireframes/v1, v2, v3.
Make the three genuinely different: one list-first, one card-first, one
search-first. Put a NOTES.md beside them saying what each version assumes about
how a person uses the app. Do not touch web/.
```

Review: the team picks one, then shows it to two people from the audience. Write what they
said in `PROCESS.md`. Only then go to stage 2.

### Stage 2: one React page on fake data

```
Here is API.md (the resource and its fields) and web/src/fake/listings.js (the
fake data from our midterm). Build the browse page in scratch/browse-page/: a
ListingsPage.jsx that reads the fake module, a Vitest test for it, and NOTES.md
saying where each file goes and which route to add to App.jsx. Match the layout
of scratch/wireframes/v2/browse.html. Tailwind only. Do not edit web/.
```

Review: move it in, run `docker compose run --rm unit` for the web tests, and have a
teammate click through it before the next page.

### Stage 3: API.md, options only

```
Here is our draft API.md and the project README's "one core resource" section.
Do not rewrite the file. For each field, list the validation rules you would
expect and why. For each endpoint, say which should require login, which admin,
and what the error body should look like for 401, 403, 404 and 422. Give me two
alternatives for how the list endpoint filters and sorts, with the trade-off.
```

Review: you write `API.md` from the options, in your own words. You will defend every line in
the lab walkthrough, so do not keep anything you cannot explain.

### Stage 4: one endpoint against its test

```
Here is API.md and tests/api/listings.spec.js, which fails for PUT
/api/listings/{id}. In scratch/update-listing/, write the controller method, the
Form Request, the policy method and the route line that make that test pass, as
separate files with NOTES.md saying where each goes in api/. Eloquent only, no
raw SQL. Validated fields only; never $request->all(). Do not edit api/.
```

Review: move it in, run `docker compose run --rm api`, open the PR, and let a teammate review
it. One endpoint per PR.

### Stage 5: beyond the minimum

Same pattern. Pick one direction from the project README, ask for options, build the
smallest version, measure it, and write the evidence in the report.

### Troubleshooting, in the main directory

```
docker compose run --rm api fails on PUT /api/listings/3 with a 419. Here is the
test output, api/routes/api.php and web/src/api.js. Explain what a 419 means in
Laravel and why this request gets one, and point me at the docs. Then suggest the
fix; if it is more than a line or two, put it in scratch/fix-419/ with NOTES.md.
```

This is the one kind of prompt that works on the project directly: it reads what is there
and explains it. The fix still comes back through `scratch/` unless it is a line you can
type yourself.

## Which tool for what

- **Chat (ChatGPT, or Claude in the browser).** It sees only what you paste. Best for
  understanding a concept, reading an error, or the stage 3 options. Paste `API.md` in; a
  plain prompt with the spec beats a clever prompt without it.
- **Editor (Cursor).** It sees your open files. Best for one function or one test while you
  watch. Read every completion you accept.
- **Agent (Claude Code, Codex).** It sees the whole repo and runs commands. Best for one small
  task with a check, like stage 4, or for troubleshooting. Read its output in `scratch/` the
  way you would read a pull request, because it is one.

More autonomy is not better. Use the least autonomous tool that can do the job.
