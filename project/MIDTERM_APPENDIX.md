# Midterm appendix — a worked example of what is expected

The midterm (Fri 10/30) asks for a front-end prototype with **no back end**:

- the two key pages from your pitch, and **a way to move between them**
- **one form** that creates or edits your core resource
- **one module of fake data** that every read and write goes through, shaped like the
  responses in your API design

This appendix is a working example of each piece. Copy it and change the names and fields to
match your resource. The example resource is a study room: `{ id, user_id, title, seats_free }`.

| Section | What it shows | Required at the midterm? |
|---|---|---|
| A | The fake-data module | **Yes** |
| B | A page that reads from it | **Yes** (loading and error states: not until the final) |
| C | A form that shows validation messages | **Yes** |
| D | Moving between the two pages | **Yes** (either way shown) |
| E | Checking it with a test | No, but worth it |
| F | What changes in week 12 | Read it: it is why A matters |

---

## A. One module of fake data — `web/src/api.js`

**Why:** in week 12 your pages will read from the real Laravel API. If every page reads data
from one file now, week 12 changes that one file and no page. Each function returns a
promise and waits a moment, because the real API will. The return values have the same
shapes as your API design (sections B and C): the list is `{ data, meta }`, a missing item
is a `404`, and bad input is a `422` with an `errors` object.

```js
// web/src/api.js -- every data call in the app goes through this file.
// Responses have the same shapes as the API design, so in week 12
// only the inside of each function changes.

let items = [
  { id: 1, user_id: 1, title: 'Folsom Library, 3rd floor', seats_free: 12 },
  { id: 2, user_id: 2, title: 'Lally 102', seats_free: 0 },
];
let nextId = 3;

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Optional: add ?fail to the address (http://localhost:5173/?fail) and every load fails.
// Useful once you add an error state (section B); not needed for the midterm.
function failIfAsked() {
  if (new URLSearchParams(window.location.search).has('fail')) {
    throw { status: 500, message: 'Server error.' };
  }
}

export async function listItems() {
  await wait(300);
  failIfAsked();
  return { data: [...items], meta: { page: 1, total: items.length } };
}

export async function getItem(id) {
  await wait(300);
  failIfAsked();
  const item = items.find((i) => i.id === Number(id));
  if (!item) throw { status: 404, message: 'No such item.' };
  return item;
}

export async function createItem(fields) {
  await wait(300);
  const errors = {};
  if (!fields.title?.trim()) errors.title = ['The title is required.'];
  else if (fields.title.length > 120) errors.title = ['The title may not be longer than 120 characters.'];
  if (Object.keys(errors).length > 0) {
    throw { status: 422, message: 'The given data was invalid.', errors };
  }
  const item = { id: nextId++, user_id: 1, ...fields };
  items = [...items, item];
  return item;
}
```

The `422` here is the fake store playing the API's part. Nobody builds a back end for the
midterm: the rules live in this file for now, and move to Laravel in week 12.

---

## B. A page that reads the data

**Why:** `useEffect` is for talking to something outside React, and the data module is
outside React. It runs after the page first appears, and that is where a page asks for its
data.

**Required at the midterm:** the page lists the items. **Not required until the final:** the
loading and error states. They are shown here because they cost a few lines and the real API
will need them; add them now if you can.

```jsx
import { useEffect, useState } from 'react';
import { listItems } from './api.js';
import NewItemForm from './NewItemForm.jsx';

export default function Browse({ onOpen }) {
  const [items, setItems] = useState([]);
  // one of: 'loading', 'ready', 'error'
  const [status, setStatus] = useState('loading');

  useEffect(() => {
    // the page may be gone before the answer arrives
    let ignore = false;
    listItems()
      .then((result) => {
        if (!ignore) { setItems(result.data); setStatus('ready'); }
      })
      .catch(() => { if (!ignore) setStatus('error'); });
    return () => { ignore = true; };
  }, []);   // []: run once, when the page appears

  if (status === 'loading') return <p>Loading…</p>;
  if (status === 'error') return <p role="alert">Could not load the list. Try again in a minute.</p>;

  return (
    <>
      <NewItemForm onCreated={(item) => setItems((prev) => [...prev, item])} />
      {items.length === 0
        ? <p>Nothing here yet. Add the first one.</p>
        : <ul>
            {items.map((item) => (
              <li key={item.id}>
                <button type="button" onClick={() => onOpen(item.id)}>{item.title}</button>
              </li>
            ))}
          </ul>}
    </>
  );
}
```

Three things to notice:

- `status` is **one value**, not `isLoading` and `isError` booleans: two booleans can
  disagree, and one value cannot (week 6, drill 9).
- **Why `ignore`?** If the person leaves the page before the data arrives, the answer still
  comes back. The flag makes it harmless. In development React runs every effect twice on
  purpose to catch exactly this.
- The **empty state** (the list loaded and has nothing in it) is different from an error. It
  is the one everyone forgets.
- `onOpen` comes from the top component, which decides how to show the other page (section D).

### The same pattern on a detail page

A detail page loads one item, so it has one more outcome: the item does not exist (`404`).
That is not the same as "something broke", so it gets its own message.

```jsx
import { useEffect, useState } from 'react';
import { getItem } from './api.js';

export default function Detail({ id, onBack }) {
  const [item, setItem] = useState(null);
  // one of: 'loading', 'ready', 'not-found', 'error'
  const [status, setStatus] = useState('loading');

  useEffect(() => {
    let ignore = false;
    getItem(id)
      .then((result) => {
        if (!ignore) { setItem(result); setStatus('ready'); }
      })
      .catch((err) => {
        if (!ignore) setStatus(err.status === 404 ? 'not-found' : 'error');
      });
    return () => { ignore = true; };
  }, [id]);   // [id]: run again if the page is showing a different item

  const back = <button type="button" onClick={onBack}>Back to the list</button>;
  if (status === 'loading') return <p>Loading…</p>;
  if (status === 'not-found') return <>{back}<p>That item does not exist.</p></>;
  if (status === 'error') return <>{back}<p role="alert">Could not load this item.</p></>;
  return (
    <article>
      {back}
      <h1>{item.title}</h1>
      <p>{item.seats_free} seats free</p>
    </article>
  );
}
```

---

## C. A form that shows validation messages

**Why:** the midterm form must tell the person what is wrong, next to the field. Here the
messages come from the fake store's `422`, so the form already works the way it will when
Laravel sends the same shape in week 12. On success it hands the new item to the page, so the
list updates without a reload.

```jsx
import { useState } from 'react';
import { createItem } from './api.js';

export default function NewItemForm({ onCreated }) {
  const [title, setTitle] = useState('');
  const [errors, setErrors] = useState({});   // the "errors" object from a 422
  const [saving, setSaving] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    setSaving(true);
    try {
      const item = await createItem({ title });
      setErrors({});
      setTitle('');
      onCreated(item);   // the parent adds it to the list: no reload
    } catch (err) {
      if (err.status === 422) setErrors(err.errors);
      else setErrors({ form: ['Could not save. Try again.'] });
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate>
      <label htmlFor="title">Title</label>
      <input
        id="title"
        value={title}
        onChange={(e) => setTitle(e.target.value)}
        aria-invalid={Boolean(errors.title)}
        aria-describedby={errors.title ? 'title-error' : undefined}
      />
      {errors.title && <p id="title-error">{errors.title[0]}</p>}
      {errors.form && <p role="alert">{errors.form[0]}</p>}
      <button type="submit" disabled={saving}>{saving ? 'Saving…' : 'Save'}</button>
    </form>
  );
}
```

If your form is on its own page rather than on the browse page, you don't need `onCreated`.
The fake data lives in `api.js`, not in the page, so when the person goes back to the list it
loads again and the new item is there.

---

## D. Moving between your two pages

A person must get from one key page to the other and back without typing an address. Either
of these is fine for the midterm:

**One piece of state** — nothing to install. The top component remembers which page is
showing:

```jsx
import { useState } from 'react';
import Browse from './Browse.jsx';
import Detail from './Detail.jsx';

export default function App() {
  const [page, setPage] = useState({ name: 'browse' });

  return page.name === 'browse'
    ? <Browse onOpen={(id) => setPage({ name: 'detail', id })} />
    : <Detail id={page.id} onBack={() => setPage({ name: 'browse' })} />;
}
```

**React Router** — `npm install react-router-dom`, then a `<BrowserRouter>` with a `<Route>`
for each page and `<Link>` to move between them (a ``<Link to={`/rooms/${item.id}`}>`` in
place of the `onOpen` button, and `useParams()` for the id on the detail page). Each page gets its own address and the back
button works. The finished app should use it, so if you have time, start here.

---

## E. Checking it with a test (optional at the midterm)

The fake delay is short, so tests simply wait for it: `findBy…` in Testing Library (HW 5) and
`expect(…).toBeVisible()` in Playwright (HW 2).

```js
// Playwright: the list shows the fake data
test('browse lists the study rooms', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByText('Folsom Library, 3rd floor')).toBeVisible();
});
```

---

## F. What changes in week 12

Only the inside of `api.js`. For example, `listItems` becomes:

```js
export async function listItems(page = 1) {
  const res = await fetch(`/api/rooms?page=${page}`, { headers: { Accept: 'application/json' } });
  if (!res.ok) throw { status: res.status, ...(await res.json()) };
  return res.json();
}
```

`fetch` does not throw on a `404` or `422`; you check `res.ok` yourself. The `throw` turns
those responses into the same error objects the fake module throws, so no page has to change.
If a page *does* have to change in week 12, it was reading data from somewhere other than
`api.js`.
