# Example 6 — The same thing, declared once

Example 5 took six commands in an exact order, and it still had a startup race. Here it
is one file and one command — against a **different stack**, so that what you learn is
about Compose and not about PHP.

```
example-6/
├── docker-compose.yml
└── simple-server/
    ├── Dockerfile
    ├── server.js          # Express + Mongoose: a visitors collection
    └── package.json
```

Node and Express instead of Apache and PHP. MongoDB instead of MySQL. Every idea from
Example 5 carries over unchanged.

---

## A — Run it

```
docker compose watch
```

Not `up` — **`watch`**. It builds, starts everything, and then stays in the foreground
watching your source for changes. This is how you will run every Compose project from
here on.

```
curl localhost:3000
curl -X POST localhost:3000/api/visitors -H "Content-Type: application/json" -d "{\"name\":\"Ada\",\"message\":\"hello\"}"
curl localhost:3000/api/visitors
```

**On Windows, type `curl.exe`, not `curl`.** In PowerShell, bare `curl` is an alias for
`Invoke-WebRequest`, which takes completely different arguments and fails confusingly.

## B — What Compose gave you that Example 5 did not

Open `docker-compose.yml` beside Example 5's README. Every line maps to something you
typed by hand there:

| You typed in Example 5 | Compose line |
|---|---|
| `docker network create itwsnet` | *(nothing — one network per project, automatic)* |
| `docker build -t ... ./web` | `build: ./simple-server` |
| `docker run -d --name db --network itwsnet ...` | the `mongo:` service |
| `docker run -d --name web --network itwsnet -p ...` | the `server:` service |
| `host=db` | `mongodb://mongo:27017` — **the service name is the hostname** |
| *(no `-p` on the database)* | *(no `ports:` on `mongo`)* |

Then look at what is new. **The race from Example 5E is gone**:

```yaml
    healthcheck:
      test: ["CMD", "mongosh", "--quiet", "--eval", "db.adminCommand('ping')"]
    ...
    depends_on:
      mongo:
        condition: service_healthy
```

`server` does not start until Mongo answers a ping. *Running* and *ready* are different
claims, and only one of them is useful. Watch it:

```
docker compose ps
```

The `mongo` row says `(healthy)`, not just `Up`.

## C — Edit code while it runs

Leave `docker compose watch` running. Open `simple-server/server.js`, change the
`message` string, save. In the watch terminal:

```
Syncing service "server" after 2 changes were detected
 Container example-6-server-1 Restarting
service(s) ["server"] restarted
```

`curl localhost:3000` again — your text. No rebuild, no manual restart, no bind mount.

That is this block in `docker-compose.yml`:

```yaml
    develop:
      watch:
        - action: sync+restart
          path: ./simple-server
          target: /app
          ignore:
            - node_modules/
        - action: rebuild
          path: ./simple-server/package.json
```

Two actions, for two different kinds of change:

- **`sync+restart`** copies the changed file in, then restarts the process. Node loaded
  `server.js` into memory when it started and will never look at the file again — so
  copying alone would do nothing.
- **`rebuild`** throws the container away and builds a new image. It fires on
  `package.json`, because a new dependency means `npm install` has to run, and that only
  happens during a build. No amount of file-copying can do it.

Try the second one: add `"dotenv": "^16.0.0"` to `dependencies` in `package.json`, save,
and watch a full rebuild instead of a sync.

The Dockerfile is arranged for exactly this:

```dockerfile
COPY package*.json ./
RUN npm install
COPY . .
```

`package.json` is copied *before* the source. Change a line of `server.js` and the
`npm install` layer is a cache hit; change a dependency and it re-runs. That is
Example 2's layer caching, used deliberately.

## D — The commands you will actually use

```
docker compose watch          # start, and sync your edits as you work
docker compose up -d          # start in the background, no watching
docker compose ps             # what is running, and is it healthy
docker compose logs -f mongo  # follow one service's logs
docker compose exec server sh # get inside
docker compose down           # stop and remove containers -- KEEPS the volume
docker compose down -v        # ... and delete the volume too
```

`down` versus `down -v` is Example 2's lesson again. Your visitors are in the
`mongo_data` volume; `down` keeps them, `down -v` does not. Prove it.

## Clean up

```
docker compose down -v
```
