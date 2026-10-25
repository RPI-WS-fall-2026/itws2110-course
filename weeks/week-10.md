# Week 10 — October 27 and October 30

[← Course home](../README.md)

| | |
|---|---|
| **Tue 10/27** | **Project work day.** No lecture: I am travelling. Come to class and work on your project with your team. The two examples in [`project/`](../project/) show where you should be: [`pantry-example-midterm`](../project/pantry-example-midterm/) for Friday, and [`pantry-example-final`](../project/pantry-example-final/) for the end of the term. |
| **Fri 10/30** | Authentication and authorization: who are you, and what are you allowed to do? Password storage, Laravel's starter kits, middleware that guards routes, and policies that decide who may edit what. **Project midterm in lab** (front-end prototype and API design). **[Homework 7](../homework/hw7/README.md) assigned** — the to-do list on a database: a migration, relationships, the N+1, and a test of your own. Due Fri 11/6. |

---

## Reading

The question for the week: **HTTP is stateless. How does a site remember who you are, and
what stops someone else from pretending to be you?** Every request arrives as a stranger.
A cookie is the browser's way of saying "it's me again". A session is the server's memory
behind that cookie. Both make logging in possible, and both open a door that this week's
readings show you how to close.

Tuesday is a project work day, so readings 1–3 are not due this week. Read them before
Homework 8 (forms and authentication, assigned Fri 11/6). Readings 4 and 5 are for Friday,
about 30 minutes. Each item says which sections to read and what to come in able to explain.

| | Reading | When |
|---|---|---|
| **Required** | 1. MDN, *Using HTTP cookies* — four sections | before **Homework 8** |
| **Required** | 2. OWASP, *CSRF Prevention Cheat Sheet* — three sections | before **Homework 8** |
| **Required** | 3. Laravel docs, *Validation*, *CSRF Protection* and *Session* — the sections below | before **Homework 8** |
| **Required** | 4. Laravel docs, *Authentication* and *Authorization* — the sections below | before **Friday** |
| **Required** | 5. OWASP, *Password Storage Cheat Sheet* — the first half | before **Friday** |
| Optional | 6. RFC 6265, section 8, *Security Considerations* | any time this week |
| Optional | 7. Stauffer, *Laravel: Up & Running*, chapters 7 and 9 | alongside Homework 8 |

### 1. MDN, *Using HTTP cookies* — required, before Homework 8

[Read it on MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Cookies)

| Read | Skip |
|---|---|
| *What cookies are used for*; *Creating, removing, and updating cookies*; *Security* (all of it: `HttpOnly`, `Secure`, `Domain`/`Path`, `SameSite`, prefixes) | *Privacy and tracking*, *Cookie-related regulations* |

Come in able to say what each of `HttpOnly`, `Secure` and `SameSite` protects against, in
one sentence each. Laravel sets all three on its session cookie for you; you can
see them in the browser's developer tools.

### 2. OWASP, *Cross-Site Request Forgery Prevention Cheat Sheet* — required, before Homework 8

[Read it on owasp.org](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)

| Read | Skip |
|---|---|
| *Introduction*; *Token-Based Mitigation* through *Synchronizer Token Pattern*; *SameSite (Cookie Attribute)* under *Defense In Depth Techniques* | the double-submit patterns, *Fetch Metadata headers*, client-side CSRF, the JavaScript section |

The attack in one line: your browser sends your cookie to a site **even when another site
made the request**. Come in able to explain why a hidden token in the form stops that, and
why the attacker's page cannot read the token. That is what Laravel's `@csrf` puts in
every form.

### 3. Laravel docs — required, before Homework 8

Three pages, read as the reference for Homework 8. Each is short in the parts listed.

| Page | Read | Skip, for now |
|---|---|---|
| [Validation](https://laravel.com/docs/13.x/validation) | *Validation Quickstart*, all of it: routes, controller, *Writing the Validation Logic*, *Displaying the Validation Errors*, *Repopulating Forms* | Form Requests, manual validators, the full rules list (look rules up when you need them), arrays, files, custom rules |
| [CSRF Protection](https://laravel.com/docs/13.x/csrf) | *Introduction*, *Preventing CSRF Requests* | excluding URIs, the `X-CSRF-TOKEN` headers (they matter in week 12 with React) |
| [Session](https://laravel.com/docs/13.x/session) | *Interacting With the Session*: *Retrieving Data*, *Storing Data*, *Flash Data*, *Regenerating the Session ID* | configuration and drivers, cache, blocking, custom drivers |

The quickstart is the whole loop you will build in Homework 8: a form posts, `validate()`
checks it, and on failure Laravel sends the browser back to the form with the errors and the
old input. Notice how: the errors ride along in the session as **flash data**, which lasts
for exactly one more request.

### 4. Laravel docs — required, before Friday

| Page | Read | Skip, for now |
|---|---|---|
| [Authentication](https://laravel.com/docs/13.x/authentication) | *Introduction* (including *Starter Kits*); *Authentication Quickstart*: *Retrieving the Authenticated User*, *Protecting Routes*, *Login Throttling*; *Manually Authenticating Users*; *Logging Out* | HTTP Basic, password confirmation, custom guards and providers, events |
| [Authorization](https://laravel.com/docs/13.x/authorization) | *Introduction*; *Gates* → *Writing Gates*, *Authorizing Actions*; *Creating Policies*; *Writing Policies* → *Policy Methods*, *Guest Users*; *Authorizing Actions Using Policies* → *Via the User Model*, *Via Middleware*, *Via Blade Templates* | gate responses and interception, policy responses and filters, Inertia |

Two words that sound alike and are not: **authentication** answers *who are you?* (logging
in), **authorization** answers *are you allowed to do this?* (editing someone else's to-do).
Come in able to give one example of each from your own project.

### 5. OWASP, *Password Storage Cheat Sheet* — required, before Friday

[Read it on owasp.org](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

| Read | Skip |
|---|---|
| *Introduction*; *Background* (*Hashing vs Encryption*, *When Password Hashes Can Be Cracked*); *Methods for Enhancing Password Storage*: *Salting*, *Using Work Factors* | peppering, the individual algorithm settings, legacy hashes |

Come in able to say why a site should never be able to tell you your own password, and what
a salt and a work factor each add. `Hash::make()`, which Laravel's starter kits use, does all
of it with bcrypt.

### 6. RFC 6265, section 8 — optional

[Read it on rfc-editor.org](https://www.rfc-editor.org/rfc/rfc6265#section-8)

The cookie standard's own *Security Considerations*, about five pages. Worth it to see that
an RFC is readable, and that the people who designed cookies listed these weaknesses
themselves in 2011.

### 7. Matt Stauffer, *Laravel: Up & Running*, 3rd ed. — optional

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/laravel-up/9781098153250/)
— sign in with your RPI credentials when prompted.

Chapter 7, *Collecting and Handling User Data* (requests and validation), for
Homework 8. Chapter 9, *User Authentication and Authorization*, for Friday. The book was
written for Laravel 10 and describes Breeze, which Laravel 13 replaced with its starter kits;
where the book and the class code disagree, the class code is right.

---

## Due this week

| | Due |
|---|---|
| **Project midterm** — front-end prototype and API design ([the project page](../project/README.md)) | **Fri 10/30, in lab** |
