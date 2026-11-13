# Week 12 — November 10 and November 13

[← Course home](../README.md)

| | |
|---|---|
| **Tue 11/10** | **Authentication and authorization.** Part 1: the two questions — who are you, and what may you do? Part 2: authentication — a password and a session cookie, then OAuth 2.0 in place of the password form and a JWT in place of the session ID. Part 3: authorization in Laravel — guards (401) and policies (403), from the pantry example. Then a step-through of the login drills. [Slides](../slides/21_authentication_v3.pdf) |
| **Fri 11/13** | **Login lab.** Team work session: add the login to your team's `api/`, with a check script that reports PASS or FAIL for each request ([`inclass/wk12/`](../inclass/wk12/README.md)). Then [the login drills](../inclass/wk12/laravel/README.md): seven holes in the to-do app to break and fix, and an eighth that writes the access rule as a policy, each with a Check result button. **Homework 8 due.** [Homework 9](../homework/hw9/README.md) assigned: the login and the write endpoints on your `api/`; due Fri 11/20. [Homework 10](../homework/hw10_optional_individual/README.md) (optional, individual) assigned: the to-do app with a login and a list of your own; due Tue 12/8. [Slides](../slides/22b_login_lab_walkthrough_v2.pdf) |

---

## Reading

| | Reading | Read |
|---|---|---|
| **Required** | 1. Laravel docs, [*Authentication*](https://laravel.com/docs/13.x/authentication) | *Authentication Quickstart*, *Manually Authenticating Users*, *Logging Out* |
| **Required** | 2. Laravel docs, [*Authorization*](https://laravel.com/docs/13.x/authorization) | *Gates*, *Creating Policies*, *Writing Policies*, *Authorizing Actions Using Policies* |
| **Required** | 3. Laravel docs, [*Sanctum*](https://laravel.com/docs/13.x/sanctum) | *Introduction*, *How it Works*, *SPA Authentication* |
| **Required** | 4. Laravel docs, [*Validation*](https://laravel.com/docs/13.x/validation), [*CSRF Protection*](https://laravel.com/docs/13.x/csrf), [*Session*](https://laravel.com/docs/13.x/session) | *Validation Quickstart*; *Preventing CSRF Requests* (incl. *X-XSRF-TOKEN*); *Interacting With the Session* |
| **Required** | 5. Laravel docs, [*Rate Limiting*](https://laravel.com/docs/13.x/routing#rate-limiting) and [*Eloquent: Query Scopes*](https://laravel.com/docs/13.x/eloquent#query-scopes) | *Defining Rate Limiters*, *Attaching Rate Limiters to Routes*; *Global Scopes* |
| **Required** | 6. Parecki, *OAuth 2.0 Simplified*: [*Authorization Code*](https://www.oauth.com/oauth2-servers/server-side-apps/authorization-code/) and [*PKCE*](https://www.oauth.com/oauth2-servers/pkce/) | both chapters |
| Optional | 7. OpenID Foundation, [*How OpenID Connect Works*](https://openid.net/developers/how-connect-works/) | the page |
| Optional | 8. Laravel docs, [*Socialite*](https://laravel.com/docs/13.x/socialite) | *Introduction* through *Retrieving User Details* |
| Optional | 9. Laravel docs, [*Passport*](https://laravel.com/docs/13.x/passport) | *Introduction*, *Authorization Code Grant With PKCE* |

---

## Due this week

| | Due |
|---|---|
| **Homework 8** — your team's hardened `api/` ([the assignment](../homework/hw8/README.md)) | **Fri 11/13, 11:59 PM** |
| **In-class** — push Friday's login lab work to your team repository ([how](../inclass/wk12/README.md)) | **Fri 11/13, end of class** |
