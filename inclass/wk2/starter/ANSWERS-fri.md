# In-class — Week 2, Friday

**Name:**
**RCS ID:**

Three questions. Answer in your own words — a short paragraph each is plenty.

---

### 1. The web container could reach the database. Your laptop could not.

Explain both halves. How did the web container find a machine called `db` when you never
configured a hostname or an IP anywhere? And why was that same database unreachable from
your own terminal?

Then say why you would *want* it that way in something real.



### 2. A container being *running* is not the same as being *ready*.

In Example 5 the page failed for a few seconds even though `docker ps` said everything
was up. Explain what was actually happening, what a healthcheck changes, and why
`depends_on` on its own was not enough.



### 3. Compose did not turn out to be a PHP tool.

You ran the same `docker compose up` against Apache + MySQL and against Node + MongoDB.
Ignoring the specific images, what is Compose actually *describing*? Name the pieces that
were identical across both stacks, and say what that means for a stack you have not met
yet.



---

**AI Use Statement** *(required)* — which tools, what for, what you changed. One or two
sentences. "I did not use AI" is a complete answer.


