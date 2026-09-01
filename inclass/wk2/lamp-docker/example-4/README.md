# Example 4 — PHP that writes to disk

*Still no database.* One container, running Apache and PHP, keeping its state in a plain text file. That constraint is the point: everything here is about **where a container's writes go and how long they last**.

```
example-4/
├── Dockerfile        # php:8.3-apache, COPY src/
└── src/index.php     # a guestbook, stored in data/visits.log
```

---

## A — Run it from a bind mount

```
docker run -d --name gb -p 8080:80 -v "$PWD/src":/var/www/html php:8.3-apache
```

Open <http://localhost:8080>, sign the guestbook a couple of times, then look on your laptop:

```
cat src/data/visits.log
```

```
2026-09-01 01:15:00	from the bind mount
```

PHP created that directory and that file. The page header tells you who did it — `www-data`, the unprivileged user Apache runs as — and on your machine the file shows up owned by *you*. Docker Desktop translates ownership across the boundary so this just works. (On native Linux it does not, and matching user IDs is a genuine chore. Be glad.)

Delete `src/data/` when you want a clean slate. Note that you can do that from your own file manager — because the data is on your machine, not in the container.

## B — Now take the mount away

```
docker rm -f gb
rm -rf src/data
docker build -t ex4-gb .
docker run -d --name gb -p 8080:80 ex4-gb
```

No `-v`. The image has its own copy of `index.php` from the `COPY` line, so the site still works. Sign the guestbook, then try two different things that sound the same:

```
docker restart gb
```

Reload. **Your entries are still there.** Restart stops and starts the process; the container's filesystem is untouched.

```
docker rm -f gb
docker run -d --name gb -p 8080:80 ex4-gb
```

Reload. **Zero entries.** Same image, same command, new container, empty guestbook.

This is the Example 1 lesson arriving in the web tier. A container's writable layer belongs to *that container* and dies with it. Nothing about "the web server" makes it different from "the database" — the rule is about containers, not about what runs inside them.

## C — A named volume, and the ownership trap

```
docker rm -f gb
docker run -d --name gb -p 8080:80 -v gb_data:/var/www/html/data ex4-gb
```

Sign it, then `docker rm -f gb` and run that same command again. Entries survive.

The interesting part is in the Dockerfile:

```dockerfile
RUN mkdir -p /var/www/html/data && chown www-data:www-data /var/www/html/data
```

Take it out and the example breaks — you get *"Could not write to /var/www/html/data/visits.log"*. Docker creates a new named volume **owned by root**, and Apache is not root. It only works because the directory exists in the image with the right owner first: when a fresh volume is mounted over a non-empty image path, Docker seeds it from that path, ownership included.

Check for yourself:

```
docker run --rm -v gb_data:/d alpine ls -ld /d
```

```
drwxr-xr-x 2 33 33 4096 /d
```

UID 33 is `www-data`. This is a genuinely common production bug, and now you have seen it on purpose instead of at 2am.

## D — Read-only, and a real error path

```
docker rm -f gb
docker run -d --name gb -p 8080:80 -v "$PWD/src":/var/www/html:ro ex4-gb
```

Try to sign it:

> Could not write to /var/www/html/data/visits.log — is it mounted read-only?

The page still renders and still lists existing entries. Reading works; writing does not. Serving your code read-only while giving the app a writable volume for *only* the directory it must write to is exactly how you would deploy this.

## Clean up

```
docker rm -f gb
docker volume rm gb_data
docker rmi ex4-gb
rm -rf src/data
```

---

## Where this leaves you

You can now run a database container, and you can run a web container. **They have not spoken to each other.** They are two isolated machines that happen to be on your laptop, and neither knows the other exists.

Making them talk is Friday.

## What you should be able to answer

Talk these through as you go. The three on `ANSWERS-tue.md` are drawn from them — you do not write up all of these.

1. `docker restart` kept your data and `docker rm` + `docker run` did not. Why is that the same rule you met with MySQL in Example 1?
2. You mounted source code read-only and data read-write. Why not both read-write?
3. A fresh named volume was owned by root, and the fix was a line in the Dockerfile. Why could you not fix it by running `chown` in the container?
4. This example stores everything in one text file. Name two things that go wrong when 60 students hit it at once.
