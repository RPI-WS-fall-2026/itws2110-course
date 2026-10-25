// web/src/api.js -- every read and every write in the app goes through this file.
//
// The midterm version answered from a fake server. This is the week-12 change the
// midterm appendix (section F) promised: only `request()` changed, from the fake server
// to fetch(). Every function below it, and every page, is the same as at the midterm.
// (One function is new: useSome, for an endpoint added after the midterm.)

/** The value of one cookie, or null. */
function readCookie(name) {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Laravel protects every request that changes something with a CSRF token (session 17).
 * Sanctum hands it out as a cookie, XSRF-TOKEN, and wants it back in a header. Ask for
 * the cookie once, before the first request that needs it.
 */
async function csrfToken() {
  if (!readCookie('XSRF-TOKEN')) {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
  }
  return readCookie('XSRF-TOKEN');
}

async function request(method, path, body) {
  const headers = { Accept: 'application/json' };
  if (method !== 'GET') headers['X-XSRF-TOKEN'] = await csrfToken();
  if (body !== undefined) headers['Content-Type'] = 'application/json';

  let res;
  try {
    res = await fetch(path, {
      method,
      credentials: 'include',            // send the session cookie
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
    });
  } catch {
    throw { status: 0, message: 'Could not reach the server.' };
  }
  const data = res.status === 204 ? null : await res.json().catch(() => null);
  // fetch does not throw on a 4xx or 5xx: check res.ok, and throw the same
  // { status, message, errors? } object the fake server threw at the midterm.
  if (!res.ok) throw { status: res.status, message: 'Something went wrong.', ...data };
  return data;
}

/** Only the filters that are set, as a query string: { location: 'fridge' } -> "?location=fridge" */
function queryString(params) {
  const set = Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== '');
  return set.length ? `?${new URLSearchParams(set)}` : '';
}

// ---------------------------------------------------------------- items: the five endpoints

/** GET /api/items -- { data: [item, ...], links, meta: { current_page, last_page, per_page, total } } */
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

/** DELETE /api/items/{id} -- nothing (204). The RA only. */
export const deleteItem = (id) =>
  request('DELETE', `/api/items/${id}`).then(() => undefined);

/** POST /api/items/{id}/use -- the updated item. Throws 422 "Not enough in stock". New since the midterm. */
export const useSome = (id, quantity) =>
  request('POST', `/api/items/${id}/use`, { quantity }).then((body) => body.data);

// ---------------------------------------------------------------- accounts (Laravel + Sanctum)

/** GET /api/user -- who is logged in. Throws 401 when nobody is. */
export const currentUser = () => request('GET', '/api/user').then((body) => body.data);

export const login = ({ email, password }) =>
  request('POST', '/api/login', { email, password }).then((body) => body.data);

export const register = (fields) =>
  request('POST', '/api/register', fields).then((body) => body.data);

export const logout = () => request('POST', '/api/logout').then(() => undefined);

/** GET /api/users -- every account, for the admin page. The RA only. */
export const listUsers = () => request('GET', '/api/users').then((body) => body.data);
