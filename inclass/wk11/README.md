# Security — find it, then fix it

[← In-class activities](../README.md)

Every app you built this term had the same holes closed for you by the framework, and you
never saw them. This week you see them open, in one small to-do app, and close them yourself.

Start at **[the week 11 security lab](laravel/README.md)**: six drills in a Laravel app with a
MySQL database, run with `docker compose watch`. Find and fix an SQL injection, an XSS, a
missing CSRF token, a secret committed in code and a dependency with a published advisory, then
add security headers. Each has a **Check result** button, and each leads with the attack — run
it first. Build the folder before class; the first run downloads PHP, MySQL and Laravel's
packages.

**Hand-in: push your copy of the workshop to your own repository at the end of class** — with
whatever you got through. Unfinished drills are fine; it counts toward participation, not
correctness.

Copy the `laravel` folder from `inclass/wk11/` in the course repo and paste it into
`inclass/wk11/` in your own repository (Finder or Explorer is fine), then commit and push.
Kept going after class? Copy, paste and push again — the latest push counts.
