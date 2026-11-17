# A front end in a bucket

[← Week 13](../../../weeks/week-13.md) · next: [the deploy drills](../laravel/README.md)

Tuesday 11/17, shown live before the drills. The simplest deploy there is: a React prototype
becomes a folder of files, and the folder goes into cloud storage that has a web address.
There is no server of ours, no container and no database.

**What you do**

1. Open the address below and use the app.
2. Answer the three predictions, then run the three `curl` lines to check yourself.
3. Decide what could not live here. That is what the rest of the week is for.

Nothing here is graded.

## The finished product

**https://itws2110labweb.z13.web.core.windows.net/**

It is the [midterm pantry prototype](../../../project/pantry-example-midterm/): two pages, a
form, fake data in the browser. Add `?as=ra` to act as the RA.

## How it got there

Three steps, the same three verbs as the rest of the week. It took forty seconds.

```bash
# BUILD: React becomes plain files. Nothing in dist/ needs Node to run.
cd project/pantry-example-midterm/web
npm ci
npx vite build
#   dist/index.html                 0.40 kB
#   dist/assets/index-….css        34.40 kB
#   dist/assets/index-….js        323.09 kB

# SHIP: copy the folder into the storage account's $web container.
az storage blob upload-batch -s dist -d '$web' --account-name itws2110labweb

# RUN: there is nothing to run. The files are the site.
```

| Word | What it means here |
|---|---|
| **Bucket** | Storage with an https address. It sends files; it runs no code. Azure calls it a storage account with a static website; AWS calls it an S3 bucket. |
| **Build** | `vite build` turns JSX, Tailwind and imports into one HTML file, one CSS file and one JavaScript file the browser can read as they are. |
| **Static** | Every visitor gets the same files. Anything that differs per visitor happens in their browser. |

## Predict, then check

Write your answer down before you run each line.

**1. Who answers the request: Laravel, Node, or neither?**

```bash
curl -I https://itws2110labweb.z13.web.core.windows.net/
```

Read the `Server:` line. There is no PHP and no Node anywhere: the storage service itself
sent `index.html`.

**2. `/items/3` is a page in the app, but there is no file called `items/3`. What status
comes back?**

```bash
curl -I https://itws2110labweb.z13.web.core.windows.net/items/3
```

`404`, and yet the page loads in a browser. The bucket was told to send `index.html` when a
file is missing, so React Router still draws the page, but the status is wrong. A real
server fixes this with a fallback route: Friday's check 4.

**3. The finished pantry asks `/api/items` for its data. What would this bucket answer?**

```bash
curl -I https://itws2110labweb.z13.web.core.windows.net/api/items
```

`404`. Nothing here can run a controller or reach a database.

## So what goes where

| | A bucket | A container (Friday) |
|---|---|---|
| Sends files | yes | yes |
| Runs your PHP | no | yes |
| Reaches a database | no | yes |
| A login that the server checks | no | yes |
| https | yes, the provider's certificate | yes, the provider's certificate |
| Cost when idle | cents a month | nothing while stopped |
| Good for | a prototype, a portfolio, a landing page | an app with an API and data |

Your midterm prototype could be deployed exactly like this. Your final project cannot: its
front end could live in a bucket, but its API and database need something that runs. Friday
puts all of it in one container instead, so there is one address and one deploy.


