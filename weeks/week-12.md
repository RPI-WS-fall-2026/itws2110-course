# Week 12 — November 10 and November 13

[← Course home](../README.md)

| | |
|---|---|
| **Tue 11/10** | **Authentication.** Part 1: logging in, from the pantry example — register, log in, the session cookie, log out, then who may do what (auth middleware, policies, mass assignment). Part 2: session cookies vs. tokens, "Log in with Google" (OAuth 2.0 and OpenID Connect), and single sign-on. [Slides](../slides/21_authentication.pdf) |
| **Fri 11/13** | **Login lab.** Team work session: add the login to your team's `api/`, with a check script that reports PASS or FAIL for each request ([`inclass/wk12/`](../inclass/wk12/README.md)). **Homework 8 due.** [Homework 9](../homework/hw9/README.md) assigned: the login and the write endpoints on your `api/`; due Fri 11/20. |

---

## Reading

| | Reading | Read |
|---|---|---|
| **Required** | 1. Laravel docs, [*Authentication*](https://laravel.com/docs/13.x/authentication) and [*Authorization*](https://laravel.com/docs/13.x/authorization) | *Authentication Quickstart*, *Manually Authenticating Users*, *Logging Out*; *Gates*, *Creating Policies*, *Writing Policies*, *Authorizing Actions Using Policies* |
| **Required** | 2. Laravel docs, [*Sanctum*](https://laravel.com/docs/13.x/sanctum#how-it-works) | *Introduction*, *How it Works* |
| **Required** | 3. MDN, [*Using HTTP cookies*](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Cookies) | *What cookies are used for*; *Creating, removing, and updating cookies*; *Security* |
| **Required** | 4. OWASP, [*Session Management Cheat Sheet*](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html) | *Introduction*; *Session ID Properties*; *Cookies*; *Renew the Session ID After Any Privilege Level Change* |
| **Required** | 5. OWASP, [*Authentication Cheat Sheet*](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html) | *Password Strength Controls*; *Store Passwords in a Secure Fashion*; *Authentication and Error Messages*; *Protect Against Automated Attacks* |
| **Required** | 6. OWASP, [*Password Storage Cheat Sheet*](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html) | *Introduction*; *Background*; *Salting*; *Using Work Factors* |
| **Required** | 7. Parecki, *OAuth 2.0 Simplified*: [*Authorization Code*](https://www.oauth.com/oauth2-servers/server-side-apps/authorization-code/) and [*PKCE*](https://www.oauth.com/oauth2-servers/pkce/) | both chapters |
| **Required** | 8. OpenID Foundation, [*How OpenID Connect Works*](https://openid.net/developers/how-connect-works/) | the page |
| **Required** | 9. Wikipedia, [*Single sign-on*](https://en.wikipedia.org/wiki/Single_sign-on) | introduction, *Benefits*, *Criticism* |
| **Required** | 10. Laravel docs, [*Validation*](https://laravel.com/docs/13.x/validation), [*CSRF Protection*](https://laravel.com/docs/13.x/csrf), [*Session*](https://laravel.com/docs/13.x/session) | *Validation Quickstart*; *Preventing CSRF Requests* (incl. *X-XSRF-TOKEN*); *Interacting With the Session* |
| Optional | 11. Auth0, [*Which OAuth 2.0 Flow Should I Use?*](https://auth0.com/docs/get-started/authentication-and-authorization-flow/which-oauth-2-0-flow-should-i-use) | |
| Optional | 12. [RFC 6749 §1](https://datatracker.ietf.org/doc/html/rfc6749#section-1) and [RFC 7636 §1](https://datatracker.ietf.org/doc/html/rfc7636#section-1) | the introductions |
| Optional | 13. Laravel docs, [*Socialite*](https://laravel.com/docs/13.x/socialite) | *Introduction* through *Retrieving User Details* |
| Optional | 14. Stauffer, [*Laravel: Up & Running*](https://learning-oreilly-com.libproxy.rpi.edu/library/view/laravel-up/9781098153250/) (RPI library) | chapters 7, 9 |

---

## Due this week

| | Due |
|---|---|
| **Homework 8** — your team's hardened `api/` ([the assignment](../homework/hw8/README.md)) | **Fri 11/13, 11:59 PM** |
| **In-class** — push Friday's login lab work to your team repository ([how](../inclass/wk12/README.md)) | **Fri 11/13, end of class** |
