# Homework 9 — Log in, and the write endpoints

**Due Friday 11/20, 11:59 PM. Team homework, in your team repo. Everyone commits.**

Homework 7 gave your project an `api/` that reads: a table, a model, seed data, and five
routes of which two work. This homework makes the other three work, behind a login. When it is
done, a person can register, log in, add one of your resource, edit their own, and be refused
someone else's, and the server, not the page, is what refuses.

You do not invent this code. A login is the same in every Laravel app, so you read it in two
finished apps and adapt it:

- **The Laravel React starter kit**, [`laravel/react-starter-kit`](https://github.com/laravel/react-starter-kit).
  This is what `laravel new` produces when you pick React: the login the Laravel team ships,
  and what thousands of real apps start from. Read it on GitHub; you do not install it.
- **The pantry example**, [`project/pantry-example-final`](../../project/pantry-example-final/).
  The same ideas in the same shape as your project (an `api/` and a `web/`), with every file
  under the same path as yours.

The examples below use a resource called `Review` at `/api/reviews`. Use your own name.
Want to see each idea fail before you build it? The optional
[login drills](../../inclass/wk12/laravel/README.md) break and fix them one at a time.

## What to do, and what is graded

1. **The login, on the API** — `POST /api/register`, `POST /api/login`, `POST /api/logout`,
   `GET /api/user`; a wrong password gets one message; the login route is rate limited.
2. **The login, on the pages** — `/login` and `/register` in your React app, the server's
   422 messages next to the fields, the header shows who is logged in with a Log out button.
3. **The write endpoints** — your three write routes work, inside `auth:sanctum`: 401 logged
   out, 422 for a bad body with a message per field, 201/200/204 for a good one. The owner
   comes from the login, never from the body.
4. **One policy** — who may edit, who may delete, as a policy class; the wrong user gets 403
   with the message your `API.md` promised; the admin may.
5. **`PROCESS.md`** — this week's entry.

The grader clones your repo, runs `docker compose watch`, opens http://localhost:8010, and
works through the list in [section 4](#4-what-your-app-should-do) by hand, then reads
`PROCESS.md`. Each of the five items is a line in the rubric.

## Why the starter kit looks different: Inertia

The starter kit has a React side: its pages are React components in `resources/js/pages/`.
What it does not have is an API. A library called [Inertia](https://inertiajs.com) sits
between Laravel and React: a Laravel route answers with the *name of a page component and
its props*, and Inertia renders that component in the browser. Your project does the same
jobs with tools you already know.

| The job | Starter kit (one app, with Inertia) | Your project (an `api/` and a `web/`) |
|---|---|---|
| Which page is this address? | Laravel's `routes/web.php` | React Router, in `App.jsx` |
| How does a page get its data? | Props, sent by the Laravel route | `fetch('/api/…')`, in `api.js` |
| How does a form submit? | Inertia's `<Form>` posts to a Laravel route | `onSubmit` calls a function in `api.js` |
| Where do the 422 messages arrive? | An `errors` prop Inertia fills in | The object `api.js` throws; `err.errors` |
| Who is logged in? | A prop shared with every page: `auth.user` | `GET /api/user`, kept by `session.jsx` |
| Who checks the password? | A package, Fortify | Your `AuthController::login()` |

That is why some files have no twin. The kit has no `routes/api.php`, no `api.js` and no
`session.jsx`, because Inertia does those jobs; and it has no login controller of its own,
because Fortify does. Neither way is more correct. A separate API is what you need when a
second client (a phone app, another team's site) will use the same data.

## Where each piece comes from

The *rules* lift across unchanged, and the *plumbing* (how a page sends a form) comes from
the pantry example. Starter-kit paths are links; pantry paths are the same as yours. The
[login walkthrough slides](../../slides/22b_login_lab_walkthrough.pdf) go through each file.

| What you need | Read it in the starter kit | What changes in yours |
|---|---|---|
| Passwords stored as hashes; `role` cannot be set by a request | [`app/Models/User.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Models/User.php): `#[Fillable]` and the `hashed` cast | Nothing. Add your `isAdmin()` and your `hasMany`. |
| What a new account must send | [`app/Actions/Fortify/CreateNewUser.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Actions/Fortify/CreateNewUser.php), with the rules in [`app/Concerns/`](https://github.com/laravel/react-starter-kit/tree/main/app/Concerns) | The same rules, inside `register()` in your `AuthController`. |
| The limit on login tries | [`app/Providers/FortifyServiceProvider.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Providers/FortifyServiceProvider.php): `RateLimiter::for('login', …)` | Goes in your `AppServiceProvider::boot()`. Write `'email'` where it says `Fortify::username()`. |
| Logging out properly | [`app/Http/Controllers/Settings/ProfileController.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Http/Controllers/Settings/ProfileController.php): the three lines in `destroy()` that log out, `invalidate()` the session and `regenerateToken()` | The same three lines in `logout()`, answering 204 instead of redirecting. |
| "Who is logged in?" for every page | [`app/Http/Middleware/HandleInertiaRequests.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Http/Middleware/HandleInertiaRequests.php): `'auth' => ['user' => $request->user()]` | Yours is a route, `GET /api/user`, that returns the same thing; `session.jsx` asks it once. |
| The two forms | [`resources/js/pages/auth/login.tsx`](https://github.com/laravel/react-starter-kit/blob/main/resources/js/pages/auth/login.tsx) and [`register.tsx`](https://github.com/laravel/react-starter-kit/blob/main/resources/js/pages/auth/register.tsx): the fields, their `autoComplete` values, an error under each field | The same fields. Yours submit with `fetch` (the pantry's `AuthPages.jsx`), not Inertia's `<Form>`. |
| The header's name and Log out | [`resources/js/components/user-menu-content.tsx`](https://github.com/laravel/react-starter-kit/blob/main/resources/js/components/user-menu-content.tsx) | The pantry's `web/src/components/Layout.jsx` is the plain version. |
| Rules written once, used by two requests | [`app/Concerns/ProfileValidationRules.php`](https://github.com/laravel/react-starter-kit/blob/main/app/Concerns/ProfileValidationRules.php), used by register and by profile update | Your resource's rules, once, used by `store()` and `update()`. |

Two things are **not** in the starter kit, so the pantry example is the only reference:

- **`login()` itself.** The kit hands it to a package (Fortify), so there is no controller to
  read. The pantry's `AuthController.php` writes the same steps out in fifteen lines.
- **A policy.** The kit has no resource that belongs to one user. The pantry's
  `ItemPolicy.php` is one.

## Two kinds of commands

As in Homework 7, nothing is installed on your laptop. Generators run in a throw-away
container that shares your folder; anything that needs the database runs inside the `app`
container that `docker compose watch` started.

```bash
docker run --rm -v "$PWD/api":/app -w /app php:8.4-cli php artisan make:policy ReviewPolicy --model=Review
```

```bash
docker compose exec app php artisan route:list --path=api
```

## 1. The login, on the API

This is Friday's lab. Five files; the table above says where each one's contents come from.

1. **`api/app/Models/User.php`.** Match the starter kit's: `password` cast to `hashed`, and
   `role` absent from `#[Fillable]`. Why: the cast means the column only ever holds a hash,
   and the allow-list means nobody registers as an admin by sending `"role": "admin"`.
2. **`api/app/Http/Controllers/Api/AuthController.php`.** Copy the pantry example's
   [`AuthController.php`](../../project/pantry-example-final/api/app/Http/Controllers/Api/AuthController.php)
   whole: `register()`, `login()`, `logout()`, `user()`. Read it beside the starter-kit files
   in the table before you commit it; you should be able to say where each method's lines
   come from.
3. **`api/app/Providers/AppServiceProvider.php`.** The `login` limiter from the starter kit,
   in `boot()`, and a `register` limiter keyed on `$request->ip()` alone. Why the email is in
   the login key: one person's typos should not lock out everyone on the same network.
4. **`api/routes/api.php`.** This one is yours, so here it is in full. The two reads stay
   open; the two account routes name their limiters; everything else needs a login.

   ```php
   use App\Http\Controllers\Api\AuthController;
   use App\Http\Controllers\ReviewController;

   Route::get('/reviews', [ReviewController::class, 'index']);
   Route::get('/reviews/{review}', [ReviewController::class, 'show']);

   Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
   Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

   Route::middleware('auth:sanctum')->group(function () {   // 401 for anyone not logged in
       Route::get('/user', [AuthController::class, 'user']);
       Route::post('/logout', [AuthController::class, 'logout']);
       Route::post('/reviews', [ReviewController::class, 'store']);
       Route::put('/reviews/{review}', [ReviewController::class, 'update']);
       Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
   });
   ```

5. **`api/bootstrap/app.php`** and **`docker-compose.yml`.** The starter kit needs neither,
   because its pages and its routes are one app. Yours are two, so one line turns the session
   on for `/api`, and one environment variable says which addresses count as your pages:

   ```php
   $middleware->statefulApi();          // inside withMiddleware
   ```

   ```yaml
   SANCTUM_STATEFUL_DOMAINS: localhost:8010,web:5173   # under app: environment:
   ```

Restart `docker compose watch` after changing the compose file. The lab's script sends the
item 1 requests for you; pass your own resource path in place of `reviews`:

```bash
bash ../itws2110-course/inclass/wk12/check-login.sh http://localhost:8010 reviews
```

## 2. The login, on the pages

Open the starter kit's `login.tsx` and `register.tsx` first: that is the finished product.
Then copy the pantry example's version of each file, which does the same with `fetch`.

1. **`web/src/api.js`.** Add `currentUser`, `login`, `register` and `logout` from the pantry's
   `api.js`. The `request()` helper you already have fetches the CSRF cookie before a write.
2. **`web/src/session.jsx`.** Copy it, and wrap your app in `<SessionProvider>` in `main.jsx`.
   It asks `GET /api/user` once when the app loads and hands the answer to any component that
   calls `useSession()`. This is your version of the starter kit's shared `auth.user`.
3. **`web/src/pages/AuthPages.jsx`.** Copy it, and add `login` and `register` routes in
   `App.jsx`. Check your forms against the starter kit's: the same fields, the same
   `autoComplete` values, and the server's message under the field it belongs to.
4. **Your layout.** Show `user.name` and a Log out button when `useSession()` has a user, a
   Log in link when it does not, as the pantry's `Layout.jsx` does.

Your midterm's `rules.js` stays: it still decides which buttons to show. What changes in
step 3 is that the server now decides whether the request behind the button is allowed.

## 3. The write endpoints, and who may do what

The three methods that answered 501. Each is short, because the work is elsewhere:
`validate()` answers 422, the policy answers 403, and route model binding answered 404 in
Homework 7.

**Validation.** The rules are your `API.md`'s field table as code. Write them once and use
them twice, for the reason the starter kit keeps its profile rules in one trait: two copies
drift apart.

```php
private const RULES = [
    'title' => ['required', 'string', 'max:120'],
    'rating' => ['required', 'integer', 'between:1,5'],
    'body' => ['nullable', 'string', 'max:2000'],
];

private const MESSAGES = [
    'title.required' => 'The title is required.',
    'rating.between' => 'The rating is 1 to 5.',
];
```

**The policy.** Generate it with the `make:policy` command above, then fill it in from the
pantry's [`ItemPolicy.php`](../../project/pantry-example-final/api/app/Policies/ItemPolicy.php):
`before()` lets the admin through everything, and `update()` and `delete()` each return one
comparison, `$review->user_id === $user->id`, or `false` if only the admin may. The rule is
the one from `PROJECT_v3.pdf` Part 1. The wrong user gets **403**, not 404, because your
resource is public: anyone may read review 1, so there is nothing to hide about it existing.

**The three methods.** The owner comes from the login, `$request->user()`, and never from
the body. `Gate::authorize` asks the policy and throws the 403 for you.

```php
use Illuminate\Support\Facades\Gate;

public function store(Request $request)
{
    $fields = $request->validate(self::RULES, self::MESSAGES);        // 422 stops here
    $review = $request->user()->reviews()->create($fields);           // the owner is the login

    return response()->json(['data' => $review->load('user')], 201);
}

public function update(Request $request, Review $review)
{
    Gate::authorize('update', $review);                               // 403 stops here
    $review->update($request->validate(self::RULES, self::MESSAGES));

    return ['data' => $review->load('user')];
}

public function destroy(Request $request, Review $review)
{
    Gate::authorize('delete', $review);
    $review->delete();

    return response()->noContent();                                   // 204
}
```

**The 403 message.** `API.md` promised one. Laravel's default is `This action is
unauthorized.`; to send yours, add a render rule in `bootstrap/app.php`'s `withExceptions`
block, as the pantry example does for `AccessDeniedHttpException`.

## 4. What your app should do

There is no autograder for this homework. This list is what the grader does by hand, in this
order, so do it yourselves first, on a fresh clone. The seeded account is `alex@example.edu`
/ `password`.

**On the pages, at http://localhost:8010**

- [ ] `/register` makes an account and leaves you logged in, with your name in the header.
- [ ] Registering again with the same email shows a message under the email field.
- [ ] Log out returns the header to a Log in link.
- [ ] `/login` with a wrong password shows one message, the same one an unknown email gets.
- [ ] `/login` as Alex works, and a page refresh keeps Alex logged in.
- [ ] Alex can add one of your resource; it appears with Alex as its owner.
- [ ] Submitting the form with a required field empty shows the server's message under that
      field, and nothing is saved.
- [ ] Alex can edit their own.
- [ ] Alex cannot edit or delete one of Priya's; the admin can.

**With `curl` or the browser's developer tools, not the page**

- [ ] `POST /api/register` with an extra `"role": "admin"` makes an ordinary user.
- [ ] `GET /api/user` logged out is 401; logged in, it is 200 with the user.
- [ ] Each of your three write routes logged out is 401.
- [ ] `POST` with a bad body is 422, with a key for each field that is wrong.
- [ ] `POST` with a good body is 201, and a `user_id` in the body is ignored.
- [ ] `PUT` and `DELETE` on someone else's row are 403, with the message from your `API.md`.
- [ ] `PUT` on your own is 200; `DELETE` on your own (or as the admin) is 204.
- [ ] Repeated bad logins for one email end in 429.

The quickest logged-out request to try:

```bash
curl -i -X DELETE http://localhost:8010/api/reviews/1
```

## 5. Submit

Write this week's `PROCESS.md` entry: who did which step, what broke, what is blocked. Commit
and push, by pull request. If `docker compose watch` does not come up clean from a fresh
clone, nothing after it can be graded; try it on a teammate's laptop first.

## Why not Fortify?

[Laravel Fortify](https://laravel.com/docs/fortify) is the package that does the starter
kit's login. The Laravel docs call it "a frontend agnostic authentication backend": it adds
the routes and controllers for log in, register, password reset, email verification and
two-factor, and has no pages of its own. So there are three ways to get a login:

| | What you write | What you get | Use it when |
|---|---|---|---|
| **By hand** (this homework) | `AuthController.php`, about 70 lines | Log in, register, log out | You are learning how a login works, which is now |
| **Fortify in your `api/`** | Config, the React pages, `GET /api/user` | The above, plus password reset, email verification, two-factor | Your project needs those extra features |
| **The starter kit** | Nothing for the login | Fortify and finished pages | You are starting a new app on Inertia |

Fortify does not replace Sanctum. Sanctum answers "is this request logged in?" (the
`auth:sanctum` on your routes); Fortify supplies the routes that log someone in. An API with
a React app can use both.

**For this homework, write it by hand.** The login is what is being taught and graded, it is
70 lines, and you should be able to explain every one. If your team's final project needs
password reset or email verification, Fortify is the sensible way to add them: ask first, and
say so in `PROCESS.md`.

## Troubleshooting

| Symptom | Cause and fix |
|---|---|
| `Class "Laravel\Fortify\Fortify" not found` | The limiter was copied from the starter kit unchanged. Replace `Fortify::username()` with `'email'`; your project does not have Fortify. |
| `Class "App\Providers\Str" not found` | The starter kit's limiter uses `Str`. Add `use Illuminate\Support\Str;` at the top of `AppServiceProvider.php`. |
| Homework 7 used `Route::apiResource` | Keep it for the two reads with `->only(['index', 'show'])`, and list the three writes inside the group as in step 1. |
| `check-login.sh` checks 1 and 4 get 500 `"Session store not set on request."` | The address you passed the check is not in `SANCTUM_STATEFUL_DOMAINS` (use `localhost`, not `127.0.0.1`), or `statefulApi()` is missing from `bootstrap/app.php`. |
| `port is already allocated` on 8010 | Another project, or Friday's lab copy, is still up. `docker compose down` in that folder. |
| Every request after login gets 419 or 401 | Sanctum does not see the request as yours: `SANCTUM_STATEFUL_DOMAINS` must include `localhost:8010`, and `statefulApi()` must be in `bootstrap/app.php`. Restart `watch` after editing the compose file. The pantry's compose file says port `5181`; yours is `8010`. |
| The name in the header is wrong right after registering | `register()` returns the user before the database filled in `role`; the example's `->fresh()` is the fix. |
| The policy never runs; everyone can delete | Laravel finds `ReviewPolicy` by name only if it is in `app/Policies/` and the model is `App\Models\Review`. Otherwise register it in `AppServiceProvider::boot()` with `Gate::policy(Review::class, ReviewPolicy::class)`. |
| A `role` of `admin` arrives through `register` | `role` is in `#[Fillable]`. Remove it. |
| The 422 has no message for a field | The field is missing from `RULES`, or the rule has no entry in `MESSAGES` and Laravel's default wording is showing. Both are fine to grade; the per-field key is what matters. |
