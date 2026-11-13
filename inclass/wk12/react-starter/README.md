# Week 12 — the Laravel React starter kit, with a Students page

[← Week 12 in-class](../README.md)

This is the [Laravel React starter kit](https://github.com/laravel/react-starter-kit): one
Laravel app whose pages are React components, joined by Inertia. It is the "one app" shape
from Friday's slides, to read beside your own project's "API and a separate React app" shape.
Nothing here is graded.

We added one page to it in class, so you can follow a request the whole way through:
a route, a controller, props, and a React page built from shadcn/ui components.

## What to do

1. Start it. From this folder:

   ```bash
   docker compose watch
   ```

   The first start builds the image and takes a few minutes. When the terminal says
   `Watch enabled`, wait about ten seconds more for Laravel and Vite to start.

2. Open http://localhost:8040/login and log in with one of the sample logins. They are
   listed on the login page itself, under the form.

3. Click **Students** in the sidebar.

4. Read the four files that made that page, in the order the request meets them:

   | # | File | Its job |
   |---|---|---|
   | 1 | `routes/web.php` | Sends `GET /students` to a controller method. |
   | 2 | `app/Http/Controllers/StudentController.php` | Gets the data and names a page: `Inertia::render('students/index', [...])`. |
   | 3 | `resources/js/pages/students/index.tsx` | The React page. `students` arrives as a prop; there is no `fetch`. |
   | 4 | `resources/js/components/app-sidebar.tsx` | The Students link in the sidebar. |

5. Watch the round trip. Open DevTools, the Network tab, then:

   - Reload `/students`. The answer is HTML, with the page's name and props inside it.
   - Click **Dashboard**, then **Students**. This request carries the header
     `X-Inertia: true`, and the answer is JSON: `component` and `props.students`.

6. Change a name in `StudentController.php` and save. Click **Students** again. The new
   name is on the screen, and you did not touch any React.

## Try it yourself

A second small case, to practise the pattern and not only this page:

- Add a `Course` page the same way: a route, a `CourseController` with two or three
  hard-coded courses, and `resources/js/pages/courses/index.tsx`. Create the files by hand,
  using the Students ones as the pattern.
- Add `'year'` to what a card shows, or sort the students in the controller. Which file
  did you have to change, the PHP or the React?

## What is in the page

| Piece | Where it comes from |
|---|---|
| `Card`, `Badge`, `Button`, `Table` | shadcn/ui. They are files in this project, under `resources/js/components/ui/`. Open one and read it. |
| The sidebar and the breadcrumb | `resources/js/app.tsx` picks the layout from the page's name. The page sets its breadcrumb at the bottom of its own file. |
| `index()` from `@/routes/students` | Generated from Laravel's route list by Wayfinder, when Vite starts and when `routes/web.php` changes. |
| The dashboard's "Signed in" card | `usePage().props.auth.user`: Laravel shares the user with every page. |

## Stopping, and starting over

```bash
docker compose down
```

The database is a SQLite file inside the container, so `down` throws away any accounts you
registered. Every start runs the migrations and the seeder again, so the sample logins come
back.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| The page shows a 500 error, or has no styling, right after starting | Laravel or Vite is still starting. Wait ten seconds and reload. |
| Port 8040 is already in use | Start with another port: `APP_PORT=8045 docker compose watch`, and open that port. |
| Port 5174 is already in use | Another copy of this app, or another Vite, is running. Stop it first. |
| An account you register asks you to verify an email | That is the starter kit's normal behaviour, and no email is sent here. Use a sample login. |
| You edited a file and nothing changed | Check the watch terminal for a `Syncing` line. Files under `resources/js/routes/` and `resources/js/actions/` are generated; do not edit them. |
| `npx shadcn@latest add <name>` | Run it on your own machine, in this folder (it needs Node 22). The watch copies the new file in, and rebuilds the image if `package-lock.json` changed. Running it inside the container works too, but the new file stays in the container. |
| `import ... from '@/routes/students'` is red in your editor | The generated route files exist only inside the container. The app still runs. To get them on your machine too, you need PHP and Node installed, and `composer install && npm install && npm run dev`. |
