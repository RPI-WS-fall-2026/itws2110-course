# Week 13 — in class

[← Week 13](../../weeks/week-13.md)

| Day | What | Counts as |
|---|---|---|
| **Tue 11/17** | [A front end in a bucket](bucket/README.md): shown live, five minutes. Then [the deploy drills](laravel/README.md): four changes that get the to-do app ready for strangers, then its production image on your laptop. | participation: push your copy to your own repository |
| **Fri 11/20** | Team work time on [Homework 10](../../homework/hw10/README.md): your project's production image, then your project on Azure. Aim to finish Part A and see your first green deploy before you leave. [Slides](../../slides/24_deployment_lab_walkthrough.pdf) | Homework 10, due Tue 12/8 |

**Before Tuesday**, so the slow downloads are done:

```bash
cd inclass/wk13/laravel
docker compose watch
```

When it says `Watch enabled`, press Ctrl+C. Then build the production image once:

```bash
docker compose -f docker-compose.prod.yml build
```

**Before Friday:** in `project/pantry-example-final`, run
`docker compose -f docker-compose.prod.yml up --build` once, open http://localhost:8090, then
Ctrl+C. That is the pantry's production image; Friday your team builds its own.
