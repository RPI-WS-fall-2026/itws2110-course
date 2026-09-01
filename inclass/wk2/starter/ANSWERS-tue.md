# In-class — Week 2, Tuesday

**Name:**
**RCS ID:**

Three questions. Answer in your own words — a short paragraph each is plenty. I am
looking for whether the idea landed, not for length, and not for the commands you ran.

---

### 1. Containers are *ephemeral*. What does that actually mean?

You lost a table in Example 1 and lost a guestbook in Example 4, and in both cases you
could make the data survive instead. Explain what "ephemeral" means for a container, why
the data disappeared when it did, and what made it stick around when it did.

Then say why the same rule applied to both the database and the web server, even though
they are completely different programs.



### 2. You edited `init.sql`, rebuilt the image, restarted the container — and nothing changed.

Explain why. What is `init.sql` actually for, when does it run, and what does that tell
you about the difference between *building an image* and *starting a container*?



### 3. You used `-v` two different ways.

`-v db1_data:/var/lib/mysql` and `-v "$PWD/site":/usr/local/apache2/htdocs` are both
volumes, and they do different jobs. Explain the difference, and say which one you would
use for code you are actively editing and which for a database's files — and why.



---

**AI Use Statement** *(required)* — which tools, what for, what you changed. One or two
sentences. "I did not use AI" is a complete answer.


