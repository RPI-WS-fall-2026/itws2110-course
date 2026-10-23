# Week 9 — October 20 and October 23

[← Course home](../README.md)

| | |
|---|---|
| **Tue 10/20** | ORM I: Eloquent, migrations and seeders — the schema as code. Objects and tables are different shapes; an ORM is the layer where they meet. Active Record, which is what Eloquent is. Then drills 1–5 of the [week 9 workshop](../inclass/wk9/laravel/), and 10–11, your own model from `make:model`, if you finish. **In-class: push your copy of the workshop to your repo at the end of class**, with whatever you finished ([how](../inclass/wk9/README.md)). |
| **Fri 10/23** | ORM II: relationships, eager loading and the N+1 problem — drills 6–9 and 12 of the same workshop — then database testing: reading the drill checks as tests. **Homework 6 due.** No new homework this week: [Homework 7](../homework/hw7/README.md), the to-do list on a database, is assigned next Friday, 10/30, and due Fri 11/6. |

Both days use one new folder, [`inclass/wk9/laravel/`](../inclass/wk9/laravel/). It runs in
Docker like week 7, with a MySQL container beside the app. Build it **before Tuesday**; the
first run downloads MySQL as well as PHP and Laravel's packages:

```bash
cd inclass/wk9/laravel
docker compose watch
```

When it says `Watch enabled`, open http://localhost:8000. If the week 7 workshop is still
running, stop it first — both use port 8000.

## The twelve drills

The pantry again, this time with a database behind it. Same **Check result** button as week 7.

- **Migrations and models** — 1. a migration is the schema, as code · 2. a model reads the
  table · 3. find one, or 404 · 4. let the database do the filtering · 5. casts
- **Relationships** — 6. belongsTo · 7. hasMany · 8. the N+1 problem · 9. belongsToMany
- **Your own model** — 10. `make:model Supplier -mf` and its migration · 11. `$fillable` and a
  factory · 12. a new migration and two relationships. 10 and 11 if you finish 1–5 on
  Tuesday, otherwise Friday; 12 on Friday, after 9.

---

## Reading

The question for the week: **objects and tables are different shapes. What happens where
they meet?** A product in your code is an object with a category. In the database it is a
row with a number in a `category_id` column. Something has to translate, every time, in
both directions — and that something has opinions.

Two pages of the Laravel docs, required, and one optional book chapter. The two pages are
the two ways to reach the database this week: plain SQL, and the ORM.

| | Reading | When |
|---|---|---|
| **Required** | 1. Laravel docs, *Database: Getting Started* — three sections | before **Tuesday** |
| **Required** | 2. Laravel docs, *Eloquent: Getting Started*, then *Eloquent: Relationships* — the sections below, not the whole pages | the first before **Tuesday**, the second before **Friday** |
| Optional | 3. Stauffer, *Laravel: Up & Running*, chapter 5 | alongside the drills and Homework 7 |

### 1. Laravel docs, *Database: Getting Started* — required, before Tuesday

[Read it on laravel.com](https://laravel.com/framework/docs/database)

How Laravel runs SQL that you write yourself, with no ORM: `DB::select('select * from
products where id = ?', [1])`, and `DB::insert`, `DB::update` and `DB::delete` beside it.
The `?` is a placeholder, and the value goes in separately, exactly as week 3's `prepare()`
did. Every drill's **In plain SQL** panel is SQL of this kind.

| Read | Skip |
|---|---|
| *Introduction*, *Running SQL Queries*, *Database Transactions*, *Connecting to the Database CLI* | *Configuration* (Docker does it for us), read and write connections, multiple connections, Vitess, query events, monitoring |

Come in able to say what `DB::select` hands back (an array of plain rows) and what the
Eloquent page's `Product::find(1)` hands back (an object). That difference is the week.

### 2. Laravel docs, *Eloquent* — required

Two pages of the current (Laravel 13) docs, one for each day:

| For | Page | Read | Skip, for now |
|---|---|---|---|
| **Tuesday**, drills 1–5 | [Eloquent: Getting Started](https://laravel.com/framework/docs/eloquent) | *Introduction*, *Generating Model Classes*, *Eloquent Model Conventions* (table names, primary keys, timestamps), *Retrieving Models*, *Retrieving Single Models / Aggregates*, *Inserting and Updating Models* (inserts, updates, mass assignment) | UUID keys, chunking, cursors, subqueries, upserts, soft deleting, pruning, scopes, events |
| **Friday**, drills 6–9 and Homework 7 | [Eloquent: Relationships](https://laravel.com/framework/docs/eloquent-relationships) | *One to Many / Has Many*, *One to Many (Inverse) / Belongs To*, *Many to Many Relationships*, *Relationship Methods vs. Dynamic Properties*, *Eager Loading* | *Has One Through*, *Has Many Through*, polymorphic relationships, pivot-table extras |

Read the introduction closely: each table has a **model**, and the model is what inserts,
updates and deletes that table's rows. On Tuesday you will write `Product::find(1)` and
`$product->save()`: the object is the row, and it saves itself. That pattern has a name,
**Active Record**, and the docs never use it; come in able to say what it means anyway.
*Eager Loading*, on Friday, is the fix for drill 8: read it for the problem it describes,
one query for the list and then one more for every row.

### 3. Matt Stauffer, *Laravel: Up & Running*, 3rd ed. — chapter 5, optional

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/laravel-up/9781098153250/)
— sign in with your RPI credentials when prompted.

Chapter 5, *Databases and Eloquent*, is the book's longest. Don't read it front to back;
use the parts that match the day:

| For | Read | Skip |
|---|---|---|
| Tuesday, drills 1–5 | migrations; seeding and factories; the start of *Introduction to Eloquent* (creating, retrieving, casting) | configuration (Docker does it for us); the raw-SQL parts of the query builder |
| Friday, drills 6–9 and Homework 7 | Eloquent relationships, including eager loading; the testing section at the end | serialization and events, for now |

The book was written for **Laravel 10**, and we use 13. Where it and the drill code
disagree, the drill code is right.

---

## Due this week

| | Due |
|---|---|
| **In-class** — your copy of `inclass/wk9/laravel/`, pushed with whatever you finished | **Tue 10/20, end of class** |
| **[Homework 6](../homework/hw6/README.md)** — the to-do list in Laravel | **Fri 10/23, 11:59 PM** |
