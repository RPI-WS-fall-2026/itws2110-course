# Example 3 — Apache, and the filesystem

*Reading: Docker: Up & Running ch. 5, ch. 6.*

Switch tiers. You have run a database; now run a web server. **No database in this example, and no PHP** — just Apache and files on disk.

The question this example answers is: *how does a file on my laptop get inside a container?*

```
example-3/
├── site/
│   ├── index.html
│   └── style.css
└── other-site/
    └── index.html      # used in part E
```

---

## A — Apache with nothing of yours in it

```
docker run -d --name web1 -p 8080:80 httpd:2.4
```

Open <http://localhost:8080>. You get **It works!** — the placeholder page baked into the image.

Where is it?

```
docker exec -it web1 bash
ls -la /usr/local/apache2/htdocs
cat /usr/local/apache2/htdocs/index.html
exit
```

`/usr/local/apache2/htdocs` is Apache's document root in this image. Anything there is served. Right now it contains someone else's placeholder.

## B — Put your files in it

```
docker rm -f web1
docker run -d --name web1 -p 8080:80 -v "${PWD}/site:/usr/local/apache2/htdocs" httpd:2.4
```

The whole `-v` argument is inside one pair of quotes on purpose. Written that way it works
unchanged in bash, zsh and PowerShell. (In `cmd` you would need `%cd%` instead of
`${PWD}` — use PowerShell.)

Reload. Your page, not theirs.

That `-v` is a **bind mount**: a directory on your machine is grafted over a directory inside the container. It is not a copy. There is exactly one set of files, visible from two places.

Prove it. Edit `site/index.html` on your laptop, change the heading, save, reload the browser. The change is already there — no rebuild, no restart, no `docker` command at all.

That instant feedback is what you want while developing. A bind mount is the crudest way to get it, and it has a cost: the container now depends on a folder on *your* machine. Example 6 gets the same speed a better way.

Now prove it in the other direction:

```
docker exec -it web1 bash
echo "<h1>Written from inside the container</h1>" > /usr/local/apache2/htdocs/hello.html
exit
ls site/
```

`hello.html` is sitting in your `site/` folder. You made it from inside a container and it landed on your laptop.

## C — Mounting over things, and read-only

The mount *hides* whatever was underneath it. `It works!` is still in the image — you just cannot see it while the mount is in the way:

```
docker exec web1 ls /usr/local/apache2/htdocs
```

Only your files. Remove the container, run it without `-v`, and the placeholder is back untouched.

A bind mount is read-write by default, which means a bug in the web server can rewrite your source code. Add `:ro`:

```
docker rm -f web1
docker run -d --name web1 -p 8080:80 -v "${PWD}/site:/usr/local/apache2/htdocs:ro" httpd:2.4
docker exec web1 sh -c 'echo test > /usr/local/apache2/htdocs/nope.html'
```

```
sh: 1: cannot create /usr/local/apache2/htdocs/nope.html: Read-only file system
```

Your laptop is still free to edit the files. The container is not.

## D — Bind mount vs. volume, side by side

You have now seen both of Docker's storage options. They are for different jobs:

| | Bind mount (`-v "${PWD}/site:/path"`) | Named volume (`-v db1_data:/path`) |
|---|---|---|
| Lives | in a folder you chose | in Docker's own storage |
| You can edit it | yes, with any editor | not directly |
| Good for | **source code you are editing** | **data the application owns** |
| Committed to git | yes — it's your project | never |

Source code in a bind mount, database files in a volume. Mixing those up is a real and common mistake.

## E — One image, two sites

There is a second folder in this directory, `other-site/`, with one page in it:

```
docker run -d --name web2 -p 8081:80 -v "${PWD}/other-site:/usr/local/apache2/htdocs" httpd:2.4
```

<http://localhost:8080> and <http://localhost:8081> are two containers from **one image**, serving different content, isolated from each other. Nothing was rebuilt, nothing was configured. This is the argument for containers in one command.

## Clean up

```
docker rm -f web1 web2
```

Then delete `site/hello.html` — the file you created from inside the container.

---

## What you should be able to answer

Talk these through as you go. The three on `ANSWERS.md` are drawn from them — you do not write up all of these.

1. You edited `index.html` and the change appeared with no rebuild. Where is that file actually stored?
2. What happened to the image's own `index.html` while your mount was in place?
3. Your database used `-v db1_data:/var/lib/mysql` and this used `-v "${PWD}/site:/..."`. Both are `-v`. What is different?
4. Why would you ever mount your source code read-only?
