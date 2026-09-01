# Example 2 — Build a MySQL image with the tables already in it

*Reading: Docker: Up & Running ch. 4 (working with Docker images).*

In Example 1 you started an empty server and typed the schema by hand. That does not survive a teammate, a laptop reimage, or a deployment. Here the schema is **part of the image**.

```
example-2/
├── Dockerfile     # FROM mysql:8.4, plus one COPY
└── init.sql       # the schema and seed data
```

The whole Dockerfile is two lines. Read them.

---

## A — Build it

```
docker build -t itws-db:1 .
```

| Piece | What it means |
|---|---|
| `build` | run the Dockerfile and produce an image |
| `-t itws-db:1` | tag it — repository `itws-db`, tag `1` |
| `.` | the **build context**: this directory is what gets sent to the builder |

The context matters. `COPY init.sql` works because `init.sql` is in the context. `COPY ../something` would fail — nothing outside the context is reachable.

```
docker image ls itws-db
docker history itws-db:1
```

`docker history` shows the layers. Your `COPY` is one thin layer on top of everything the MySQL image already was.

## B — Run it

```
docker run --name db2 -e MYSQL_ROOT_PASSWORD=root -d itws-db:1
docker logs -f db2
```

In the log you will see the entrypoint announce it is running `/docker-entrypoint-initdb.d/init.sql`. Wait for `ready for connections`, then:

```
docker exec -it db2 mysql -uroot -proot app
```

```sql
SHOW TABLES;
SELECT s.name, c.code
FROM students s
JOIN enrollments e ON e.student_id = s.id
JOIN courses c     ON c.id = e.course_id
ORDER BY s.name;
```

Data is there and you never typed it. Note also that we published **no ports** this time — nothing outside Docker can reach this database. That is a deliberate default, and Example 5 depends on it.

## C — The gotcha that will cost you an hour someday

Add a row to `init.sql`:

```sql
INSERT INTO students (name, class_year) VALUES ('Katherine Johnson', 2026);
```

Rebuild and restart:

```
docker build -t itws-db:1 .
docker rm -f db2
docker run --name db2 -e MYSQL_ROOT_PASSWORD=root -d itws-db:1
docker exec -it db2 mysql -uroot -proot app -e "SELECT * FROM students"
```

It works — because that container had no volume, so every start is a fresh empty data directory.

Now do the same thing **with** a volume, the way a real project runs:

```
docker rm -f db2
docker run --name db2 -e MYSQL_ROOT_PASSWORD=root -v db2_data:/var/lib/mysql -d itws-db:1
```

Add another `INSERT` to `init.sql`, rebuild, `docker rm -f db2`, and run that same command again. **Your change does not appear.** The scripts in `/docker-entrypoint-initdb.d/` run *only when the data directory is empty*, and the volume is not empty any more.

The fix, and the reason you will type it constantly:

```
docker rm -f db2
docker volume rm db2_data
```

Nine out of ten "my schema change isn't showing up" problems in this course are this. When it happens, the database is not broken — it is doing exactly what it was told.

## Clean up

```
docker rm -f db2
docker volume rm db2_data
```

---

## What you should be able to answer

Talk these through as you go. The three on `ANSWERS-tue.md` are drawn from them — you do not write up all of these.

1. What is the build context, and what is it for?
2. Why does `docker build` finish almost instantly the second time you run it?
3. If you edit `init.sql`, which layers get rebuilt — and which are reused?
4. You have a populated volume and a real schema change to ship. Deleting the volume deletes production data. So what do real teams do instead?
