# Week 3 — September 8 and 11

[← Course home](../README.md)

| | |
|---|---|
| **Tue 9/8** | Testing, both ends: a unit test on one PHP function, and an end-to-end test that drives a browser. In-class exercise. |
| **Fri 9/11** | Continuous integration. **Homework 1 due; Homework 2 assigned. Teams form.** |

---

## Reading — due Tuesday

The question for the week: **is a test a verification of code, or a specification of behavior?** The answer changes what you write — and what you write at the bottom of the test pyramid is not what you write at the top.


### 1. Mauro Chojrin, *Modern Testing with PHP* — chapters 1, 2, 7

[Read it through the RPI library](https://learning-oreilly-com.libproxy.rpi.edu/library/view/modern-testing-with/9798868823268/) — sign in with your RPI credentials when prompted. If you are off campus and it will not load, go to the library site first, then follow the link again.

| Chapter | | |
|---|---|---|
| 1 | About Automated Tests | what a test is for, and why "it works when I click it" does not count |
| 2 | Introduction to PHPUnit | your first real test file: arrange, act, assert, and reading a failure |
| 3 | Case Study | Read about the case study. We will use it in the homework. |



### 2. Playwright — *Writing tests*, 15 minutes

[playwright.dev/docs/writing-tests](https://playwright.dev/docs/writing-tests)

Playwright drives a real browser from a script and asserts what a user would see. It is language-agnostic — it does not know or care that the server is PHP. Read the page; do not install anything — on Tuesday it runs inside a container. You will write Playwright tests yourself in Homework 2.

Optional
### 3. Ian Cooper, *TDD, Where Did It All Go Wrong* — 1 hour, video

[youtube.com/watch?v=EZ05e7EMOLM](https://www.youtube.com/watch?v=EZ05e7EMOLM) (DevTernity 2017)

Argues that most people test the wrong unit and end up with suites that block every refactor. This will change how you test, and it is the talk I will be arguing with in class. Watch it at 1.5× if you must, but watch all of it.



---

## Due this week

| | Due |
|---|---|
| In-class exercise | Tue 9/8, 11:59 PM |
| **Homework 1** — the LAMP stack | **Fri 9/11, 11:59 PM** |

Friday is a lab: bring your laptop with Docker Desktop running and Homework 1 in a state you are willing to show someone. **Teams form Friday**, and it is the last day to add or drop without a W.
