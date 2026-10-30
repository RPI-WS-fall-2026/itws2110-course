# Week 11 — November 3 and November 6

[← Course home](../README.md)

| | |
|---|---|
| **Tue 11/3** | Security. The OWASP Top 10:2025 as a map of what goes wrong; then the six holes you will fix on Friday, each shown first as an attack and then as the one line that closes it: injection (SQL and otherwise), cross-site scripting, cross-site request forgery, security headers and TLS, secrets, and the supply chain. Every example is code you have already written: the to-do list from Homework 6 and the pantry project. |
| **Fri 11/6** | **Security Lab.** Six drills in one small Laravel app, [`inclass/wk11/laravel/`](../inclass/wk11/laravel/): find and fix an SQL injection, an XSS, a missing CSRF token, a secret committed in code and a dependency with a published advisory, then add security headers. Same **Check result** button as weeks 7 and 9. **In-class: push your copy of the workshop to your repo at the end of class**, with whatever you finished ([how](../inclass/wk11/README.md)). **Homework 7 due.** Homework 8 (forms and authentication) is assigned; due Fri 11/13. |

Build the lab folder **before Friday**. It runs like week 9, with a MySQL container beside the app;
the first run downloads PHP, MySQL and Laravel's packages:

```bash
cd inclass/wk11/laravel
docker compose watch
```

When it says `Watch enabled`, open http://localhost:8000. If the week 9 workshop or Homework 7
is still running, stop it first (`docker compose down` in its folder): both use port 8000.

## The six drills

The to-do list again, with six holes in it. Each drill opens with the attack, so you see it
work before you fix it.

- **Input** — 1. SQL injection: a search box that dumps the users table · 2. XSS: a note
  that runs in everyone's browser
- **Requests** — 3. CSRF: another site adds to-dos to your list · 4. a secret committed in code
- **The platform** — 5. a dependency with a published advisory · 6. security headers

---

## Reading

The question for the week: **is security a feature you add, or a property of how the
system was designed?** Every input your app receives was typed by a stranger, and every
request your server answers could have been sent by anyone. The framework closed most of
the doors for you — `{{ }}`, `@csrf`, bound query parameters, `.env` — and this week's
readings are about the doors that are still open, and the habit of noticing them.

Three readings before Tuesday, about 45 minutes. Two before Friday, about 25 minutes; they
are the reference for the drills. Two more are the reference for Homework 8 and are due with
it, not this week. The rest are optional. (The cookie, authentication and password readings
planned for week 10 are here: CSRF is required, the Laravel pages go with Homework 8, and the
others are optional.) Each item says which sections to read and what to come in able to explain.

| | Reading | When |
|---|---|---|
| **Required** | 1. OWASP, *Top 10:2025* — the introduction and four of the ten entries | before **Tuesday** |
| **Required** | 2. OWASP, *Cross Site Scripting Prevention Cheat Sheet* — the first half | before **Tuesday** |
| **Required** | 3. OWASP, *CSRF Prevention Cheat Sheet* — three sections | before **Tuesday** |
| **Required** | 4. Laravel docs, *Blade*, *Configuration* and *Sanctum* — one section of each | before **Friday** |
| **Required** | 5. MDN, *Practical security implementation guides* — four of the fourteen | before **Friday** |
| **Required** | 6. Laravel docs, *Validation*, *CSRF Protection* and *Session* — the sections below | with **Homework 8** |
| **Required** | 7. Laravel docs, *Authentication* and *Authorization* — the sections below | with **Homework 8** |
| Optional | 8. MDN, *Using HTTP cookies* — four sections | any time this week |
| Optional | 9. OWASP, *Password Storage Cheat Sheet* — the first half | any time this week |
| Optional | 10. GitHub, *Our plan for a more secure npm supply chain*, and the `composer audit` reference | any time this week |
| Optional | 11. Saltzer and Schroeder, *The Protection of Information in Computer Systems* (1975) — the eight design principles | any time this week |
| Optional | 12. Wikipedia, *XZ Utils backdoor* — two sections | any time this week |
| Optional | 13. Stauffer, *Laravel: Up & Running*, chapters 7 and 9 | alongside Homework 8 |
| Optional | 14. McDonald, *Web Security for Developers* — chapters 6, 7 and 8 | alongside the lab and Homework 9 |

### 1. OWASP, *Top 10:2025* — required, before Tuesday

[Read it on owasp.org](https://top10.owasp.org/2025/) — start with
[the introduction](https://top10.owasp.org/2025/0x00_2025-Introduction/).

The Top 10 is a ranked list of the kinds of bug that cause the most damage, rebuilt from
data every four years. It is the vocabulary of the field: every job posting, audit and
findings report uses these names. The 2025 edition came out this year.

| Read | Skip |
|---|---|
| Introduction: *What's changed in the Top 10 for 2025* | *Methodology* and everything after it |
| [A01 Broken Access Control](https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/): *Description*, *Example attack scenarios* | the *Score table* and the *List of Mapped CWEs* on every entry |
| [A02 Security Misconfiguration](https://top10.owasp.org/2025/A02_2025-Security_Misconfiguration/): *Description*, *How to prevent* | |
| [A03 Software Supply Chain Failures](https://top10.owasp.org/2025/A03_2025-Software_Supply_Chain_Failures/): *Description*, *Example attack scenarios* | |
| [A05 Injection](https://top10.owasp.org/2025/A05_2025-Injection/): *Description*, *How to prevent*, *Example attack scenarios* | |
| The titles of the other six, from the list on the front page | their pages |

Come in able to say which category each of Friday's six drills belongs to — four of them
are in the entries above — and what moved between 2021 and 2025: injection fell from
third to fifth, misconfiguration rose to second, and supply chain failures are new. Tuesday
opens by asking why.

### 2. OWASP, *Cross Site Scripting Prevention Cheat Sheet* — required, before Tuesday

[Read it on owasp.org](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

| Read | Skip |
|---|---|
| *Introduction*; *Framework Security*; *XSS Defense Philosophy*; *Output Encoding* → *HTML Contexts*, *HTML Attribute Contexts*, *JavaScript Contexts* | *CSS Contexts*, *URL Contexts*, *HTML Sanitization*, *Safe Sinks*, *Other Controls*, *Common Anti-patterns* |

The idea the sheet keeps repeating: the same text is safe in one place on the page and
dangerous in another, so encode for the *context* you are writing into. Blade's `{{ }}` is
the HTML-context encoder (week 7, drill 4). Come in able to say what `{!! !!}` turns off,
and name one place where `{{ }}` is not enough (hint: inside a `<script>` tag). Drill 1, SQL injection, has no reading: its fix is the
`prepare()` and `?` placeholders you have used since week 3, and every `where()` Eloquent
wrote for you in week 9.

### 3. OWASP, *Cross-Site Request Forgery Prevention Cheat Sheet* — required, before Tuesday

[Read it on owasp.org](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)

| Read | Skip |
|---|---|
| *Introduction*; *Token-Based Mitigation* through *Synchronizer Token Pattern*; *SameSite (Cookie Attribute)* under *Defense In Depth Techniques* | the double-submit patterns, *Fetch Metadata headers*, client-side CSRF, the JavaScript section |

The attack in one line: your browser sends your cookie to a site **even when another site
made the request**. Come in able to explain why a hidden token in the form stops that, and
why the attacker's page cannot read the token. That is what Laravel's `@csrf` puts in
every form, and what drill 3 on Friday puts back.

### 4. Laravel docs — required, before Friday

Three short sections, one per drill. Read each as the reference for the drill it names.

| Page | Read | For |
|---|---|---|
| [Blade](https://laravel.com/docs/13.x/blade#displaying-data) | *Displaying Data*, down to the end of *HTML Entity Encoding* | drill 2, XSS |
| [Configuration](https://laravel.com/docs/13.x/configuration#environment-configuration) | *Environment Configuration*, through *Retrieving Environment Configuration*; note the paragraph headed *Environment File Security* | drill 4, secrets |
| [Sanctum](https://laravel.com/docs/13.x/sanctum#how-it-works) | *Introduction* → *How it Works* only | drill 3, CSRF, and your project |

Come in able to say why `env()` belongs in `config/` files and nowhere else, and why the
pantry example's React front end keeps its login in a cookie rather than a token stored in
JavaScript. (The Sanctum page says it in one sentence; reading 8, on `HttpOnly` cookies, says why it
matters.)

### 5. MDN, *Practical security implementation guides* — required, before Friday

[The index, on MDN](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides)

Fourteen one-page guides, each one header or setting. Read these four; they are drill 6.

| Read | Skip |
|---|---|
| [CSP implementation](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/CSP) | the other ten guides, and *User information security* |
| [Clickjacking prevention](https://developer.mozilla.org/en-US/docs/Web/Security/Attacks/Clickjacking) | |
| [MIME type verification](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/MIME_types) | |
| [TLS configuration](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/TLS) — the *HTTP redirection* and *HTTP Strict Transport Security implementation* sections | the cipher suite tables |

Come in able to name the header each guide is about, and say in one sentence what each one
tells the browser to refuse. These are the four headers Friday's last drill adds to the
to-do app, and the ones Homework 9's findings report will ask you about.

### 6. Laravel docs — required, with Homework 8

Three pages, read as the reference for Homework 8. Each is short in the parts listed.

| Page | Read | Skip, for now |
|---|---|---|
| [Validation](https://laravel.com/docs/13.x/validation) | *Validation Quickstart*, all of it: routes, controller, *Writing the Validation Logic*, *Displaying the Validation Errors*, *Repopulating Forms* | Form Requests, manual validators, the full rules list (look rules up when you need them), arrays, files, custom rules |
| [CSRF Protection](https://laravel.com/docs/13.x/csrf) | *Introduction*, *Preventing CSRF Requests* | excluding URIs, the `X-CSRF-TOKEN` headers (they matter in the API lab, Fri 11/13, with React) |
| [Session](https://laravel.com/docs/13.x/session) | *Interacting With the Session*: *Retrieving Data*, *Storing Data*, *Flash Data*, *Regenerating the Session ID* | configuration and drivers, cache, blocking, custom drivers |

The quickstart is the whole loop you will build in Homework 8: a form posts, `validate()`
checks it, and on failure Laravel sends the browser back to the form with the errors and the
old input. Notice how: the errors ride along in the session as **flash data**, which lasts
for exactly one more request.

### 7. Laravel docs — required, with Homework 8

| Page | Read | Skip, for now |
|---|---|---|
| [Authentication](https://laravel.com/docs/13.x/authentication) | *Introduction* (including *Starter Kits*); *Authentication Quickstart*: *Retrieving the Authenticated User*, *Protecting Routes*, *Login Throttling*; *Manually Authenticating Users*; *Logging Out* | HTTP Basic, password confirmation, custom guards and providers, events |
| [Authorization](https://laravel.com/docs/13.x/authorization) | *Introduction*; *Gates* → *Writing Gates*, *Authorizing Actions*; *Creating Policies*; *Writing Policies* → *Policy Methods*, *Guest Users*; *Authorizing Actions Using Policies* → *Via the User Model*, *Via Middleware*, *Via Blade Templates* | gate responses and interception, policy responses and filters, Inertia |

Two words that sound alike and are not: **authentication** answers *who are you?* (logging
in), **authorization** answers *are you allowed to do this?* (editing someone else's to-do).
Homework 8 asks for one example of each from your own project.

### 8. MDN, *Using HTTP cookies* — optional

[Read it on MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Cookies)

| Read | Skip |
|---|---|
| *What cookies are used for*; *Creating, removing, and updating cookies*; *Security* (all of it: `HttpOnly`, `Secure`, `Domain`/`Path`, `SameSite`, prefixes) | *Privacy and tracking*, *Cookie-related regulations* |

Read it if cookies are new to you. Be able to say what each of `HttpOnly`, `Secure` and `SameSite` protects against, in
one sentence each. `HttpOnly` is why drill 2 (XSS) cannot steal a login, and `SameSite` is
half of the answer to drill 3 (CSRF). Laravel sets all three on its session cookie for you; you can
see them in the browser's developer tools.

### 9. OWASP, *Password Storage Cheat Sheet* — optional

[Read it on owasp.org](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

| Read | Skip |
|---|---|
| *Introduction*; *Background* (*Hashing vs Encryption*, *When Password Hashes Can Be Cracked*); *Methods for Enhancing Password Storage*: *Salting*, *Using Work Factors* | peppering, the individual algorithm settings, legacy hashes |

Be able to say why a site should never be able to tell you your own password, and what
a salt and a work factor each add. `Hash::make()`, which Laravel's starter kits use, does all
of it with bcrypt.

### 10. GitHub, *Our plan for a more secure npm supply chain* — optional

[Read it on github.blog](https://github.blog/security/supply-chain-security/our-plan-for-a-more-secure-npm-supply-chain/)
(September 2025, about eight minutes), then the
[`composer audit`](https://getcomposer.org/doc/03-cli.md#audit) and
[`npm audit`](https://docs.npmjs.com/cli/v11/commands/npm-audit) command references —
the *Description* section of each.

The blog post is npm's response to the Shai-Hulud worm, which spread by publishing
itself into packages whose maintainers' tokens it had stolen; the OWASP entry you read
for Tuesday names it. Your project installs a few hundred packages you have never read,
through `composer install` and `npm install`. Drill 5 explains what an `audit`
command checks, where its list of advisories comes from, and what a `postinstall`
script is allowed to do on your laptop. Drill 5 runs `composer audit` on the lab app and
finds something real.

### 11. Saltzer and Schroeder, *The Protection of Information in Computer Systems* — optional

[Read it at virginia.edu](https://www.cs.virginia.edu/~evans/cs551/saltzer/) — section
I.A.3, *Design Principles*, about three pages. 1975, and every one of the eight principles
is still quoted: least privilege, fail-safe defaults, complete mediation, economy of
mechanism. Tuesday's summary slide maps each of Friday's drills onto one of them.

### 12. Wikipedia, *XZ Utils backdoor* — optional

[Read it on wikipedia.org](https://en.wikipedia.org/wiki/XZ_Utils_backdoor) — *Background*
and *Mechanism*. The other supply chain story worth knowing: two years of patient
social engineering to become a maintainer, a backdoor in a compression library that
nearly reached every Linux server, and one engineer who noticed a login took half a
second too long.

### 13. Matt Stauffer, *Laravel: Up & Running*, 3rd ed. — optional

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/laravel-up/9781098153250/)
— sign in with your RPI credentials when prompted.

Chapter 7, *Collecting and Handling User Data* (requests and validation), and chapter 9,
*User Authentication and Authorization*, both for Homework 8. The book was
written for Laravel 10 and describes Breeze, which Laravel 13 replaced with its starter kits;
where the book and the class code disagree, the class code is right.

### 14. Malcolm McDonald, *Web Security for Developers* — optional

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/-/9781098122683/)
— sign in with your RPI credentials when prompted.

Chapter 6, *Injection Attacks*; chapter 7, *Cross-Site Scripting Attacks*; chapter 8,
*Cross-Site Request Forgery Attacks*. Written for a reader at exactly this point: it
explains how each attack works before the fix, in any language. The OWASP sheets are the
reference; this is the one to read if the sheets feel like a list of rules.

---

## Due this week

| | Due |
|---|---|
| **Homework 7** — your team's Laravel back end ([the assignment](../homework/hw7/README.md)) | **Fri 11/6, 11:59 PM** |
| **In-class** — push `inclass/wk11/laravel` to your repository, with whatever you finished ([how](../inclass/wk11/README.md)) | **Fri 11/6, end of class** |
