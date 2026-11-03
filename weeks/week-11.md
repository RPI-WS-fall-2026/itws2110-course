# Week 11 — November 3 and November 6

[← Course home](../README.md)

| | |
|---|---|
| **Tue 11/3** | **Security.** The OWASP Top 10:2025, then the six holes you fix on Friday: injection, XSS, CSRF, secrets, the supply chain, and security headers. Each is shown as an attack, then as the line that closes it. [Slides](../slides/19_security.pdf) · [OWASP Top 10 reference](../slides/19b_owasp_top10.pdf) |
| **Fri 11/6** | **Security Lab.** Six drills in one small Laravel app, [`inclass/wk11/laravel/`](../inclass/wk11/laravel/), with the **Check result** button. Push your copy to your repo at the end of class ([how](../inclass/wk11/README.md)). **Homework 7 due.** [Homework 8](../homework/hw8/README.md) assigned: the same six defenses on your team's `api/`; due Fri 11/13. |

Build the lab folder **before Friday**. Stop the week 9 workshop or Homework 7 first if it is
running; both use port 8000.

```bash
cd inclass/wk11/laravel
docker compose watch
```

When it says `Watch enabled`, open http://localhost:8000.

---

## Reading

| | Reading | Read |
|---|---|---|
| **Required** | 1. OWASP, [*Top 10:2025*](https://top10.owasp.org/2025/) | [Introduction](https://top10.owasp.org/2025/0x00_2025-Introduction/); [A01](https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/), [A02](https://top10.owasp.org/2025/A02_2025-Security_Misconfiguration/), [A03](https://top10.owasp.org/2025/A03_2025-Software_Supply_Chain_Failures/), [A05](https://top10.owasp.org/2025/A05_2025-Injection/) |
| **Required** | 2. OWASP, [*Cross Site Scripting Prevention Cheat Sheet*](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html) | Introduction through *Output Encoding* (HTML, attribute and JavaScript contexts) |
| **Required** | 3. OWASP, [*CSRF Prevention Cheat Sheet*](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) | Introduction; *Synchronizer Token Pattern*; *SameSite* |
| **Required** | 4. Laravel docs: [*Blade*](https://laravel.com/docs/13.x/blade#displaying-data), [*Configuration*](https://laravel.com/docs/13.x/configuration#environment-configuration), [*Sanctum*](https://laravel.com/docs/13.x/sanctum#how-it-works), [*Middleware*](https://laravel.com/framework/docs/13.x/middleware) | *Displaying Data*; *Environment Configuration*; *How it Works*; *Defining Middleware*, *Registering Middleware* |
| **Required** | 5. MDN, [*Practical security implementation guides*](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides) | [CSP](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/CSP), [Clickjacking](https://developer.mozilla.org/en-US/docs/Web/Security/Attacks/Clickjacking), [MIME types](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/MIME_types), [TLS](https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/TLS) (HSTS section) |
| Optional | 6. GitHub, [*Our plan for a more secure npm supply chain*](https://github.blog/security/supply-chain-security/our-plan-for-a-more-secure-npm-supply-chain/); [`composer audit`](https://getcomposer.org/doc/03-cli.md#audit); [`npm audit`](https://docs.npmjs.com/cli/v11/commands/npm-audit) | |
| Optional | 7. Saltzer and Schroeder, [*The Protection of Information in Computer Systems*](https://www.cs.virginia.edu/~evans/cs551/saltzer/) (1975) | §I.A.3, *Design Principles* |
| Optional | 8. Wikipedia, [*XZ Utils backdoor*](https://en.wikipedia.org/wiki/XZ_Utils_backdoor) | *Background*, *Mechanism* |
| Optional | 9. McDonald, [*Web Security for Developers*](https://learning-oreilly-com.libproxy.rpi.edu/library/view/-/9781098122683/) (RPI library) | chapters 6, 7, 8 |

---

## Due this week

| | Due |
|---|---|
| **Homework 7** — your team's Laravel back end ([the assignment](../homework/hw7/README.md)) | **Fri 11/6, 11:59 PM** |
| **In-class** — push `inclass/wk11/laravel` to your repository ([how](../inclass/wk11/README.md)) | **Fri 11/6, end of class** |
