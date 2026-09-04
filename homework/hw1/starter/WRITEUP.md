# Homework 1 — Write-up

**Due Friday, September 11, 11:59 PM**, with the rest of `homework/hw1/`.

**Name:**
**RCS ID:**

Three questions. Answer in your own words — a short paragraph each is plenty. I am
looking for whether the idea landed, not for length, and not for a list of commands.

---

### 0. What broke?

Something did. Say what, and what fixed it. If nothing broke, say so, and say what you
think you did differently from the person next to you. Two or three sentences.



### 1. `watch` — where does your code actually live?

You ran `docker compose watch`, edited `src/index.html`, and the change appeared without
a rebuild. Yet the Dockerfile still says `COPY src/`, and there is no bind mount.

Explain what `watch` did when you saved the file, and why the `COPY` line is still there.
Then: if you had used a bind mount instead — the way Example 3 did — what would be true of
your image on a machine that is not yours?



### 2. Compose — *started* is not *ready*

Your `web` service waits on `db` with `condition: service_healthy`, not just `depends_on`.

Explain what the healthcheck is actually asking the database, and what you would have
seen on the very first `docker compose up` without it. Why is this a property of the
*system* rather than a Docker quirk — where else this semester do you expect to meet it?



### 3. `down` vs `down -v` — where does the data live?

Your seed rows come from `init.sql`, which is baked into the MySQL image. But if you edit
`init.sql` and run `docker compose up --build`, the rows do not change.

Explain why. Which command fixes it, and what does that tell you about the difference
between where the *schema* lives and where the *data* lives? Finish with one sentence on
what you would do instead if those rows were real users and you could not delete them.



---




