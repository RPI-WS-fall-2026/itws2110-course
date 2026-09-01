# Example 1 — Run MySQL with no Dockerfile

*Reading: Docker: Up & Running ch. 5 (containers), ch. 6 (getting inside a running container).*

You have not written a line of configuration and you are about to have a running database server. That is the whole point of this step. Nothing here is built — we pull somebody else's image and run it.

There are no files in this directory. Everything happens on the command line.

---

## Before you start — which terminal?

Every command in this example is the same on macOS, Linux and Windows. Two things about
Windows are worth knowing before you hit them:

- **Use PowerShell or Windows Terminal.** Docker Desktop must be running.
- **If you use Git Bash**, `docker exec -it` fails with *"the input device is not a
  TTY."* That is MinTTY, not Docker. Either prefix the command with `winpty`
  (`winpty docker exec -it db1 bash`) or use PowerShell for this example.

The commands below are written on one line each so they paste identically everywhere. If
you reformat one across several lines, the continuation character differs by shell —
`\` in bash, a backtick in PowerShell, `^` in `cmd`. That mismatch is the single most
common reason a copied Docker command fails on Windows.

## A — Start it

```
docker run --name db1 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=app -p 3306:3306 -d mysql:8.4
```

Read that flag by flag before you press enter:

| Flag | What it does |
|---|---|
| `--name db1` | names the container so you can refer to it later |
| `-e KEY=value` | sets an environment variable *inside* the container |
| `-p 3306:3306` | publishes container port 3306 to host port 3306 |
| `-d` | detached — run in the background, give me my prompt back |
| `mysql:8.4` | the image: repository `mysql`, tag `8.4` |

`MYSQL_ROOT_PASSWORD` and `MYSQL_DATABASE` are not Docker features. They are the *MySQL image's* interface — read the image's documentation on Docker Hub to find out what an image accepts.

Now watch it come up:

```
docker ps
docker logs -f db1
```

The first boot takes 10–30 seconds. Wait for `ready for connections`, then `Ctrl-C` to stop following the log. `Ctrl-C` here stops *watching*, not the container — confirm that with `docker ps`.

## B — Get a shell inside it

Before you touch MySQL, get a shell on the machine it is running on. Because that is what a container is — a Linux machine, and you can walk around inside it.

```
docker exec -it db1 bash
```

`docker exec` runs a *new* process inside a container that is already running. `-it` gives that process an interactive terminal. Your prompt changes, and you are now root on a host that did not exist a minute ago.

Look around:

```
whoami                      # root
cat /etc/os-release         # Oracle Linux 9 -- not whatever your laptop runs
ls /var/lib/mysql           # the actual database files
head -20 /etc/my.cnf        # the server's configuration
cat /proc/1/cmdline; echo   # process 1 is mysqld itself
```

Two things are worth stopping on.

**Process 1 is `mysqld`.** On your laptop, PID 1 is the init system and the database is one of hundreds of processes. Here the database *is* the machine. Kill it and the container is over — there is nothing else to keep running. That is why `docker stop` and "stop the database" are the same action.

**Almost nothing is installed.** Try these:

```
ps aux
vim /etc/my.cnf
```

Both fail — no `ps`, no editor. This is not a broken image; it is a deliberate one. An image ships what the application needs and nothing else, because every extra package is more to download, more to patch, and more for an attacker to use. (`microdnf install procps-ng` would add `ps`, but resist — anything you install this way disappears with the container. Changes belong in a Dockerfile, which is Example 2.)

### ...then start MySQL, from in there

You are on the machine. The client is right there on the PATH:

```
mysql -uroot -proot
```

```sql
SHOW DATABASES;
USE app;
CREATE TABLE students (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100));
INSERT INTO students (name) VALUES ('Ada Lovelace'), ('Grace Hopper');
SELECT * FROM students;
EXIT;
```

`EXIT` leaves the MySQL client and drops you back to the container's bash prompt. `exit` again leaves the container and returns you to your own machine. **Two prompts, two exits** — losing track of which one you are typing at is the most common confusion of the day, so check with `whoami` if you are unsure.

The client greets you with this, and every non-interactive `mysql` command will print it too:

```
mysql: [Warning] Using a password on the command line interface can be insecure.
```

It is right, and you will see it all semester. In a real deployment the password comes from a secrets manager, not from your shell history — Example 6 takes one step in that direction.

### The shortcut

Now that you know what is actually happening, the short form makes sense:

```
docker exec -it db1 mysql -uroot -proot
```

Same `docker exec`, but the process it starts is `mysql` instead of `bash`. There was never a bash session in the middle; you were just using one as a place to stand. This is the form you will type from here on — and when it behaves oddly, drop back to `bash` and look around.

## C — Connect from outside the container

You published port 3306, so the host can reach it. If you have a MySQL client installed:

```
mysql -h 127.0.0.1 -P 3306 -uroot -proot app
```

If you don't, borrow one from a throwaway container:

```
docker run -it --rm mysql:8.4 mysql -h host.docker.internal -P 3306 -uroot -proot app
```

`--rm` deletes the container the moment you exit. Use it for anything disposable.

**Question for your notes:** the second command runs the same image you're already running. Why is it a client and not a second database server?

## D — Destroy it, and lose everything

```
docker stop db1
docker rm db1
```

Now run the exact `docker run` from part A again, get back in, and `SELECT * FROM students;`.

The table is gone. **A container's filesystem dies with the container.** This is the single most important thing in this example, and it is why the next command exists:

```
docker rm -f db1
docker run --name db1 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=app -p 3306:3306 -v db1_data:/var/lib/mysql -d mysql:8.4
```

`-v db1_data:/var/lib/mysql` mounts a **named volume** — storage managed by Docker, living outside the container — over the directory where MySQL keeps its files. Recreate your table, then `docker rm -f db1` and run it again. This time the data is still there.

```
docker volume ls
docker volume inspect db1_data
```

## Clean up

```
docker rm -f db1
docker volume rm db1_data
```

---

## What you should be able to answer

Talk these through as you go. The three on `ANSWERS.md` are drawn from them — you do not write up all of these.

1. What is the difference between an image and a container?
2. Why did `-p 3306:3306` have to be there for part C but not for part B?
3. Where does a named volume actually live, and why isn't it in this folder?
4. You changed `MYSQL_DATABASE` and re-ran with the same volume. Nothing changed. Why? *(You will hit this for real in Example 2.)*
