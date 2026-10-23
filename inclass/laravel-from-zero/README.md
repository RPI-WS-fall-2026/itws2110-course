# Laravel from zero: a new project, your own models, and both kinds of relationship

In the drills and homework you edited models someone else set up. Here you start with an
empty folder and build the two relationship patterns from class yourself:

```
ONE-TO-MANY                           MANY-TO-MANY
customers         orders              students        enrollments        courses
  id  ◄──────────── customer_id         id  ◄────────── student_id         id
  name              total               name            course_id ──────►  title
                                                        grade
```

- **One-to-many:** the foreign key sits on the "many" side. `orders.customer_id` points at
  `customers.id`.
- **Many-to-many:** a junction table, `enrollments`, holds one row per pairing, so it is
  really two one-to-many links. It can carry its own data: the `grade`.

**What you need:** Docker, and nothing else. No PHP or Composer on your laptop.
**Time:** about 45 minutes, plus a few minutes of downloading the first time.

| Step | What you do | Pattern |
|---|---|---|
| 1 | Create a new Laravel project | |
| 2 | Run it in Docker, with MySQL | |
| 3 | Generate `Customer` and `Order` | one-to-many |
| 4 | Connect them, seed them, show them | one-to-many |
| 5 | Generate `Student`, `Course` and the `enrollments` table | many-to-many |
| 6 | Connect them, seed them, show them | many-to-many |

Every step ends with **Done when**. If something goes wrong, see
[When something breaks](#when-something-breaks) at the end.

---

## Step 1 · Create the project

**Why:** `composer create-project` downloads Laravel's skeleton (the folders, config and
starter files) and every package it needs. We run Composer inside the official Composer
image, so it never needs installing. `-v "$PWD":/app` shares your current folder with the
container, so the project lands on your laptop.

From the folder where you keep your projects:

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer create-project laravel/laravel relationships
```

```bash
cd relationships
```

It takes two or three minutes. It ends by running a few migrations into a SQLite file. That
is Laravel's default; Step 2 switches it to MySQL, like the rest of the course.

**Done when:** you have a `relationships/` folder with `app/`, `database/`, `routes/` and
`vendor/` in it.

---

## Step 2 · Run it in Docker, with MySQL

**Why:** the project needs PHP to run and MySQL to store data. Two containers do that. This
time the whole project folder is shared into the `app` container (`.:/app`), so files that
`artisan` generates inside the container appear in your editor straight away.

Create **`Dockerfile`** in the project folder:

```dockerfile
FROM php:8.4-cli
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

Create **`docker-compose.yml`**:

```yaml
services:
  app:
    build: .
    ports:
      - "8020:8000"
    volumes:
      - .:/app
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mysql:8.4
    environment:
      MYSQL_DATABASE: relationships
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - dbdata:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "-uroot", "-psecret"]
      interval: 3s
      retries: 40

volumes:
  dbdata:
```

In **`.env`**, find the `DB_` lines and make them say this (remove the `#` in front of the
ones that have one):

```ini
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=relationships
DB_USERNAME=root
DB_PASSWORD=secret
```

`DB_HOST=db` is the service name in the compose file: containers find each other by name.
The password is fine for a class exercise; a real project keeps it out of git (`.env` is
already in `.gitignore`).

Start it, then build the tables. Use a second terminal for the second command:

```bash
docker compose up --build
```

```bash
docker compose exec app php artisan migrate
```

**Done when:** http://localhost:8020 shows the Laravel welcome page, and `migrate` lists
three migrations as `DONE`.

From here on, every `artisan` command runs inside the container:
`docker compose exec app php artisan …`

---

## Step 3 · Generate `Customer` and `Order`

**Why:** `make:model` writes the files you would otherwise copy by hand. `-m` adds a
migration and `-f` a factory. **Order matters:** migrations run in the order of the
timestamp in their file names, and `orders` points at `customers`, so `customers` must
exist first. Make `Customer` first.

```bash
docker compose exec app php artisan make:model Customer -mf
```

```bash
docker compose exec app php artisan make:model Order -mf
```

Each prints three files: `app/Models/…`, `database/factories/…Factory.php` and
`database/migrations/…_create_…_table.php`. Open them; the model is nearly empty, and the
migration has only `id()` and `timestamps()`.

Now describe the columns. In the **customers** migration, add one line after `$table->id();`:

```php
$table->string('name');
```

In the **orders** migration, add two:

```php
$table->foreignId('customer_id')->constrained()->cascadeOnDelete();
$table->decimal('total', 8, 2);
```

- `foreignId('customer_id')` makes the column. `constrained()` reads its name and adds the
  rule that it must match a real `customers.id`.
- `cascadeOnDelete()` deletes a customer's orders when the customer is deleted. Without it,
  the database refuses to delete a customer who has orders.
- `decimal(8, 2)` stores money exactly (up to 999999.99). A float would round.

```bash
docker compose exec app php artisan migrate
```

**Done when:** `migrate` runs `create_customers_table` and then `create_orders_table`, in
that order, with no error.

---

## Step 4 · Connect them, seed them, show them

**Why:** the foreign key is in the database now, but PHP does not know about it yet. A
relationship method on each model tells Eloquent how to follow it, in both directions.

**The models.** In `app/Models/Customer.php`, inside the class, under `use HasFactory;`:

```php
protected $fillable = ['name'];

public function orders()
{
    return $this->hasMany(Order::class);
}
```

In `app/Models/Order.php`:

```php
protected $fillable = ['customer_id', 'total'];

public function customer()
{
    return $this->belongsTo(Customer::class);
}
```

Same column, read from both sides: `hasMany` on the "one" side, `belongsTo` on the "many"
side, the one that holds the key. `$fillable` lists the columns that `create([...])` may
fill (week 9, drill 11).

> Laravel 13's own `User` model lists them with an attribute instead, `#[Fillable([...])]`
> above the class. Both work; we use `$fillable` because the drills did.

**The factories** make fake rows. In `CustomerFactory.php`, replace `//` in `definition()`:

```php
'name' => fake()->name(),
```

In `OrderFactory.php`, replace `//`, and add `use App\Models\Customer;` with the other `use`
lines at the top:

```php
'customer_id' => Customer::factory(),
'total' => fake()->randomFloat(2, 5, 200),
```

`Customer::factory()` means "make a customer for this order if none is given".

**The seeder** fills the tables. In `database/seeders/DatabaseSeeder.php`, add
`use App\Models\Customer;` and `use App\Models\Order;` at the top, and at the end of `run()`:

```php
// One customer by hand, so you know exactly what to look for.
$ana = Customer::create(['name' => 'Ana Lopez']);
$ana->orders()->create(['total' => 42.50]);
$ana->orders()->create(['total' => 18.00]);

// Three more from the factories, each with two orders.
Customer::factory(3)->has(Order::factory()->count(2))->create();
```

`$ana->orders()->create([...])` fills in `customer_id` for you: that is the relationship at
work.

**A page.** At the end of `routes/web.php`:

```php
Route::get('/customers', function () {
    $customers = \App\Models\Customer::with('orders')->orderBy('name')->get();

    return view('customers', ['customers' => $customers]);
});
```

`with('orders')` loads every customer's orders in one extra query, not one per customer:
the N+1 fix from drill 8.

Create `resources/views/customers.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Customers</title></head>
<body>
    <h1>Customers</h1>
    <ul>
        @foreach ($customers as $customer)
            <li>
                {{ $customer->name }} — {{ $customer->orders->count() }} orders,
                ${{ number_format($customer->orders->sum('total'), 2) }} in total
            </li>
        @endforeach
    </ul>
</body>
</html>
```

Rebuild the tables and seed them:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

**Done when:** http://localhost:8020/customers lists four customers, including
**Ana Lopez — 2 orders, $60.50 in total**. Then follow the relationship the other way, in
tinker:

```bash
docker compose exec app php artisan tinker
```

```php
App\Models\Order::first()->customer->name   // "Ana Lopez"
```

(Type `exit` to leave tinker.)

---

## Step 5 · Generate `Student`, `Course` and the `enrollments` table

**Why:** a student takes many courses and a course has many students, so neither table can
hold the other's id. The junction table holds the pairs. It has no model of its own, so it
gets a plain migration, made **after** the two tables it points at.

```bash
docker compose exec app php artisan make:model Student -mf
```

```bash
docker compose exec app php artisan make:model Course -mf
```

```bash
docker compose exec app php artisan make:migration create_enrollments_table
```

Laravel reads `create_enrollments_table` and fills in `Schema::create('enrollments', …)`.

Add the columns, each after `$table->id();`. **students:**

```php
$table->string('name');
```

**courses:**

```php
$table->string('title');
```

**enrollments:**

```php
$table->foreignId('student_id')->constrained()->cascadeOnDelete();
$table->foreignId('course_id')->constrained()->cascadeOnDelete();
$table->string('grade', 2)->nullable();
$table->unique(['student_id', 'course_id']);
```

- Two foreign keys: one per side. That is the "two one-to-many links".
- `grade` belongs to the **pairing**, not to the student or the course: Sam's grade in Web
  Systems. That is why it lives here. `nullable()`, because a grade comes at the end of term.
- `unique([...])` stops the same student enrolling in the same course twice.

```bash
docker compose exec app php artisan migrate
```

**Done when:** `migrate` runs students, courses, then enrollments, with no error.

---

## Step 6 · Connect them, seed them, show them

**The models.** In `app/Models/Student.php`, under `use HasFactory;`:

```php
protected $fillable = ['name'];

public function courses()
{
    return $this->belongsToMany(Course::class, 'enrollments')
                ->withPivot('grade')
                ->withTimestamps();
}
```

In `app/Models/Course.php`:

```php
protected $fillable = ['title'];

public function students()
{
    return $this->belongsToMany(Student::class, 'enrollments')
                ->withPivot('grade')
                ->withTimestamps();
}
```

- `belongsToMany` on **both** sides: neither side holds the key.
- `'enrollments'` names the junction table. Without it Laravel looks for its default name,
  both models in alphabetical order: `course_student` (drill 9's `product_tag`).
- `withPivot('grade')` brings the extra column along. Read it as `$course->pivot->grade`.
- `withTimestamps()` fills the junction table's `created_at` and `updated_at`.

**The factories.** In `StudentFactory.php` replace `//` with `'name' => fake()->name(),`
and in `CourseFactory.php` with `'title' => fake()->words(3, true),`.

**The seeder.** Add `use App\Models\Course;` and `use App\Models\Student;` at the top of
`DatabaseSeeder.php`, and at the end of `run()`:

```php
// Many-to-many: attach() writes one row into enrollments per pair.
$web = Course::create(['title' => 'Web Systems Development']);
$db = Course::create(['title' => 'Database Systems']);

$sam = Student::create(['name' => 'Sam Rivera']);
$sam->courses()->attach($web->id, ['grade' => 'A']);
$sam->courses()->attach($db->id, ['grade' => 'B+']);

$kim = Student::create(['name' => 'Kim Chen']);
$kim->courses()->attach($web->id);   // no grade yet
```

**A page for one student.** At the end of `routes/web.php`:

```php
Route::get('/students/{student}', function (\App\Models\Student $student) {
    return view('student', ['student' => $student]);
});
```

`{student}` and `Student $student` match, so Laravel finds the row or answers 404 (route
model binding, drill 3).

Create `resources/views/student.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>{{ $student->name }}</title></head>
<body>
    <h1>{{ $student->name }}</h1>
    <ul>
        @forelse ($student->courses as $course)
            <li>{{ $course->title }} — grade: {{ $course->pivot->grade ?? 'not yet' }}</li>
        @empty
            <li>Not enrolled in anything.</li>
        @endforelse
    </ul>
</body>
</html>
```

```bash
docker compose exec app php artisan migrate:fresh --seed
```

**Done when:**

- http://localhost:8020/students/1 shows **Sam Rivera** with Web Systems Development (A)
  and Database Systems (B+)
- http://localhost:8020/students/2 shows Kim Chen in Web Systems, grade **not yet**
- http://localhost:8020/students/99 is a 404
- in tinker, the other direction works too:

```php
$c = App\Models\Course::where('title', 'Web Systems Development')->first();
$c->students->pluck('name')                // ["Sam Rivera", "Kim Chen"]
DB::table('enrollments')->count()          // 3: one row per pairing
```

---

## What you built

| | One-to-many | Many-to-many |
|---|---|---|
| Where the foreign key lives | on the "many" side: `orders.customer_id` | in a junction table: `enrollments` |
| Migration | one `foreignId()->constrained()` | two, plus `unique([...])` |
| Methods | `hasMany` on the one, `belongsTo` on the many | `belongsToMany` on both sides |
| Making a link | `$customer->orders()->create([...])` | `$student->courses()->attach($id, [...])` |
| Extra data on the link | (not needed) | `withPivot('grade')`, read as `->pivot->grade` |

The commands, in the order you used them:

| Command | Does |
|---|---|
| `composer create-project laravel/laravel name` | a new project |
| `php artisan make:model Name -mf` | a model, its migration and its factory |
| `php artisan make:migration create_things_table` | a migration on its own (junction tables) |
| `php artisan migrate` | runs the migrations that have not run yet |
| `php artisan migrate:fresh --seed` | drops every table, rebuilds, seeds |
| `php artisan tinker` | try your models by hand |

When you are done: `docker compose down` stops it. Add `-v` to throw the database away too.

---

## When something breaks

| What you see | What to do |
|---|---|
| `create-project` takes minutes | Normal the first time: it is downloading Laravel and its packages |
| `port is already allocated` | Something else uses 8020. Change `"8020:8000"` to `"8021:8000"` and use that port |
| `SQLSTATE[HY000] [2002] Connection refused` or `getaddrinfo for db failed` | `.env` still points at SQLite or 127.0.0.1. Check every `DB_` line from Step 2, then `docker compose restart app` |
| `Failed to open the referenced table 'customers'` | The orders migration ran before customers. Delete both migration files and the models, and run `make:model Customer` first |
| `Table 'relationships.course_student' doesn't exist` | `belongsToMany` is missing its second argument, `'enrollments'` |
| `Add [name] to fillable property` | The model has no `$fillable`, or the column is missing from it |
| `Duplicate entry … for key 'enrollments_student_id_course_id_unique'` | That student is already in that course: the `unique` rule working |
| `Attempt to read property "grade" on null` | `withPivot('grade')` is missing, or you read `$course->grade` instead of `$course->pivot->grade` |
| You ran `make:model` and see no new files | Run it with `docker compose exec app …` from inside the project folder; the `.:/app` volume is what puts the files on your laptop |
| `Class "Database\Factories\Customer" not found` | `OrderFactory.php` is missing `use App\Models\Customer;` at the top |
