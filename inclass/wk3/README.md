# Week 3 — Testing, both ends

| | |
|---|---|
| **Tue 9/8** | A unit test on one PHP function, then an end-to-end test that drives a real browser. Both run in containers. |
| **Fri 9/11** | Continuous integration. **Homework 1 due. Homework 2 assigned — you will test Grocy, a real PHP app that shipped with no tests.** Teams form. |

**One hand-in for the week: `ANSWERS.md`, three questions on Tuesday's exercises, due
Tuesday 9/8 at 11:59 PM.** It counts toward participation.

## Get the answer sheet

Copy [`starter/ANSWERS.md`](starter/) into `inclass/wk3/` in **your own private repository**:

```bash
cp -R ../itws2110-course/inclass/wk3/starter/. inclass/wk3/
```

---

## Tuesday

| | | |
|---|---|---|
| 1 | [A unit test, and a function that fails it](example-1-unit/) | PHPUnit · red first · read the failure · make it green |
| 2 | [An end-to-end test that drives a browser](example-2-e2e/) | Playwright · the week-2 guestbook · what a unit test cannot see |

Do them in order. Example 1 is a *specification* you make true. Example 2 is the same idea
one level up — the specification is "a person can use this page," and the test is a robot
being that person.

## Submit

Commit to `main` and push. No branch, no pull request.

```bash
git add inclass/wk3/
git commit -m "In-class week 3"
git push
```

Your answers go in `inclass/wk3/ANSWERS.md` **in your repository**, by tonight.
