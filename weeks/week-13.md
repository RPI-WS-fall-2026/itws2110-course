# Week 13 — November 17 and November 20

[← Course home](../README.md)

| | |
|---|---|
| **Tue 11/17** | **Cloud deployment: build, ship, run.** The finished product first: the pantry, live. Part 1: what is different about a copy that strangers use. Part 2, build: one production image in three stages. Part 3, ship and run: a registry, Azure Container Apps, settings and secrets, what https (TLS) protects and who holds the certificate, migrations that keep the data, revisions and rolling back. Part 4, the pipeline: tests, then deploy, with no stored password. In class: [a front end in a bucket](../inclass/wk13/bucket/README.md), the midterm prototype deployed as plain files, then [the deploy drills](../inclass/wk13/laravel/README.md): four changes that get the to-do app ready for strangers, then its production image on your laptop. [Slides](../slides/23_deployment.pdf) |
| **Fri 11/20** | **Team time on [Homework 11](../homework/hw11/README.md).** Your project gets a production image, then an `https://` address on Azure that a `git push` deploys to, with a check script that reports PASS or FAIL for each thing a deployed copy must do. Aim to see your first green deploy before you leave. **Homework 9 due.** |

Do this **before Tuesday**: `cd inclass/wk13/laravel` and `docker compose watch`, then open
http://localhost:8030 and stop it with Ctrl+C. Then, in the same folder,
`docker compose -f docker-compose.prod.yml build`. The first runs download and build the images.

Do this **before Friday**, so the slow downloads are finished before class. In your copy of
the course repo:

```bash
cd project/pantry-example-final
docker compose -f docker-compose.prod.yml up --build
```

When the `app` container says it is serving, open http://localhost:8090, register an
account, then press Ctrl+C. That is the pantry's production image: Friday your team starts building its own (Homework 11).

---

## Reading

| | Reading | Read |
|---|---|---|
| **Required** | 1. Wiggins, [*The Twelve-Factor App*](https://12factor.net/) | [III. Config](https://12factor.net/config), [V. Build, release, run](https://12factor.net/build-release-run), [VI. Processes](https://12factor.net/processes), [X. Dev/prod parity](https://12factor.net/dev-prod-parity), [XI. Logs](https://12factor.net/logs). Five short pages. |
| **Required** | 2. Laravel docs, [*Deployment*](https://laravel.com/docs/13.x/deployment) | *Optimization*, *Debug Mode*, *The Health Route* |
| **Required** | 3. Docker docs, [*Multi-stage builds*](https://docs.docker.com/build/building/multi-stage/) | through *Stop at a specific build stage* |
| **Required** | 4. The pantry example's production files | [`Dockerfile`](../project/pantry-example-final/Dockerfile), [`docker-compose.prod.yml`](../project/pantry-example-final/docker-compose.prod.yml), [`deploy.yml`](../project/pantry-example-final/.github/workflows/deploy.yml). Read the comments; Homework 11 copies these three. |
| **Required** | 5. Microsoft Learn, [*Azure Container Apps overview*](https://learn.microsoft.com/en-us/azure/container-apps/overview) and [*Update and deploy changes*](https://learn.microsoft.com/en-us/azure/container-apps/revisions) | the overview; *Revisions* through *Revision modes* |
| **Required** | 6. GitHub docs, [*OpenID Connect*](https://docs.github.com/en/actions/deployment/security-hardening-your-deployments/about-security-hardening-with-openid-connect) | *Overview of OpenID Connect*, *Benefits of using OIDC* |
| **Required** | 7. MDN, [*Transport Layer Security*](https://developer.mozilla.org/en-US/docs/Web/Security/Defenses/Transport_Layer_Security) and [*Strict-Transport-Security*](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Strict-Transport-Security) | the TLS page through *The TLS 1.3 handshake*; the header page's *Description*. Week 11 sent this header; this week you see it work |
| Optional | 8. Microsoft Learn, [*Set scaling rules*](https://learn.microsoft.com/en-us/azure/container-apps/scale-app) | *Scale definition*; what "scale to zero" costs and saves |
| Optional | 9. FrankenPHP docs, [*Laravel*](https://frankenphp.dev/docs/laravel/) | the server inside the production image |
| Optional | 10. GitHub docs, [*Using jobs in a workflow*](https://docs.github.com/en/actions/writing-workflows/choosing-what-your-workflow-does/using-jobs-in-a-workflow) | *Defining prerequisite jobs*: the `needs:` line |

---

## Due this week

| | Due |
|---|---|
| **Homework 9** — the login and the write endpoints on your team's `api/` ([the assignment](../homework/hw9/README.md)) | **Fri 11/20, 11:59 PM** |
| **In-class** — push your copy of Tuesday's deploy drills to your own repository, for participation ([how](../inclass/wk13/laravel/README.md#hand-in)) | **Tue 11/17, end of class** |
| **Homework 11** (team) — your project live on Azure ([the assignment](../homework/hw11/README.md)); started in class Friday | **Fri 12/4, 11:59 PM** |
