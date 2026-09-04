# Example 5 — Two containers that talk, without Compose

*Reading: Docker: Up & Running ch. 6 (exploring Docker).*

A database alone is not an application. Here PHP runs in one container and MySQL runs in another, and they find each other over a network you create yourself.

We do this by hand first so that Compose, in Example 6, reads as *shorthand for things you already understand* rather than as magic.

```
example-5/
├── db/
│   ├── Dockerfile      # same as Example 2
│   └── init.sql
└── web/
    ├── Dockerfile      # php:8.3-apache + pdo_mysql
    └── src/index.php   # queries "db" by container name
```

---

## A — Create a network

```
docker network create itwsnet
docker network ls
```

A user-defined network gives you one thing a default bridge does not: **DNS by container name**. That is the whole trick in `index.php`, which connects to a host called `db`.

## B — Build both images

```
docker build -t itws-db:1  ./db
docker build -t itws-web:1 ./web
```

## C — Run them, both on the network

```
docker run -d --name db  --network itwsnet -e MYSQL_ROOT_PASSWORD=root itws-db:1
docker run -d --name web --network itwsnet -p 8080:80 itws-web:1
```

Look at what is *not* on the `db` line: no `-p`. The database publishes nothing to your machine. Only containers on `itwsnet` can reach it. The web container is the only way in, which is exactly how you want a production database configured.

Give MySQL ten seconds to finish its first boot, then open <http://localhost:8080>.

## D — Prove the network is doing the work

```
docker exec -it web bash
```

Inside the web container:

```
getent hosts db
```

You get the `db` container's IP. Nothing configured that — Docker's DNS did. Now break it on purpose:

```
exit
docker network disconnect itwsnet db
```

Reload the page. You get the failure page, and the error names the cause exactly:

```
SQLSTATE[HY000] [2002] php_network_getaddresses:
getaddrinfo for db failed: Name or service not known
```

That is a **DNS** failure, not a database failure. MySQL is still running, perfectly healthy, one `docker ps` away. The name just stopped resolving. Put it back:

```
docker network connect itwsnet db
```

Do it to the `web` container instead and you get something different — a dead connection rather than an error page, because disconnecting `web` takes its published port with it. Worth trying, and worth explaining.

Then inspect who is attached:

```
docker network inspect itwsnet
```

## E — The race you will hit

Kill both and start them in the *wrong* order, quickly:

```
docker rm -f web db
docker run -d --name db  --network itwsnet -e MYSQL_ROOT_PASSWORD=root itws-db:1
docker run -d --name web --network itwsnet -p 8080:80 itws-web:1
```

Now reload <http://localhost:8080> immediately. You will probably get the failure page, and it will fix itself in a few seconds.

`db` being *started* is not the same as MySQL being *ready to accept connections*. Every distributed system has this problem. Example 6 shows the standard fix.

## Clean up

```
docker rm -f web db
docker network rm itwsnet
```

---

## What you should be able to answer

Talk these through as you go. Nothing is handed in.

1. Why can the web container reach `db` when your laptop cannot?
2. You ran six commands to start this. What breaks when a teammate runs five of them, in a different order, with a different network name?
3. The database password is `root`, hardcoded in `index.php` and passed on the command line. Name two things wrong with that.
