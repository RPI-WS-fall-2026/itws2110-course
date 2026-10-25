// web/src/api.js -- every read and every write in the app goes through this file.
//
// MIDTERM_APPENDIX.md section A, with one change: the appendix puts the fake data inside
// each function. Here each function builds a request (method, path, body) and hands it to
// `request()`, and only `request()` knows the data is fake. So in week 12 one function
// changes -- `request()` calls fetch() instead of the fake server -- and no page and no
// other function in this file changes. (Appendix section F is the same idea.)
//
// The functions return what a page needs (the item, the list and its meta). On an error
// they throw { status, message, errors? } -- the same object for a fake 422 now and a
// real 422 in week 12, so every page's error handling already works.

import { createFakeServer } from './fake-server.js';

// Flags for trying the prototype, added to the address:
//   ?slow   every request takes 2 seconds, to see each page's loading state
//   ?fail   every load fails, to see each page's error state (appendix section A)
//   ?reset  throw away the changes made in this tab and start from the seed data
//   ?as=priya, ?as=ra, ?as=guest  act as another person (there is no log in yet)
const flags = new URLSearchParams(typeof window === 'undefined' ? '' : window.location.search);
const storage = typeof window === 'undefined' ? null : window.sessionStorage;
if (flags.has('reset')) storage?.clear();

const server = createFakeServer({ storage });
if (flags.has('as')) server.actAs(flags.get('as'));

// The real API will take a moment; so does the fake one, so loading states get seen.
const DELAY_MS = flags.has('slow') ? 2000 : 250;
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function request(method, path, body) {
  await wait(DELAY_MS);
  if (flags.has('fail') && method === 'GET') {
    throw { status: 500, message: 'Server error.' };
  }
  // structuredClone: the page gets its own copy, as it would from a real network
  // response, so nothing a page does can change the fake server's data by accident.
  const response = server.handle(method, path, structuredClone(body));
  if (response.status >= 400) throw { status: response.status, ...response.body };
  return structuredClone(response.body);

  // ---- Week 12: the body of this function becomes ----
  // const res = await fetch(path, {
  //   method,
  //   credentials: 'include',                      // send the Sanctum session cookie
  //   headers: { Accept: 'application/json', 'Content-Type': 'application/json',
  //              'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') },
  //   body: body === undefined ? undefined : JSON.stringify(body),
  // });
  // const data = res.status === 204 ? null : await res.json();
  // if (!res.ok) throw { status: res.status, ...data };   // fetch does not throw on 4xx
  // return data;
}

/** Only the filters that are set, as a query string: { location: 'fridge' } -> "?location=fridge" */
function queryString(params) {
  const set = Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== '');
  return set.length ? `?${new URLSearchParams(set)}` : '';
}

// ---------------------------------------------------------------- items: the five endpoints

/** GET /api/items -- { data: [item, ...], meta: { current_page, last_page, per_page, total } } */
export const listItems = ({ location, status, page } = {}) =>
  request('GET', `/api/items${queryString({ location, status, page })}`);

/** GET /api/items/{id} -- the item. Throws a 404 if there is no such item. */
export const getItem = (id) =>
  request('GET', `/api/items/${id}`).then((body) => body.data);

/** POST /api/items -- the created item. Throws 401 or 422. */
export const createItem = (fields) =>
  request('POST', '/api/items', fields).then((body) => body.data);

/** PUT /api/items/{id} -- the updated item. Send only the fields that change. */
export const updateItem = (id, fields) =>
  request('PUT', `/api/items/${id}`, fields).then((body) => body.data);

/** DELETE /api/items/{id} -- nothing (204). The RA (admin) only. */
export const deleteItem = (id) =>
  request('DELETE', `/api/items/${id}`).then(() => undefined);

// ---------------------------------------------------------------- who you are

/** GET /api/user -- who is logged in. Throws 401 when nobody is. Log in itself comes in week 12. */
export const currentUser = () => request('GET', '/api/user').then((body) => body.data);
