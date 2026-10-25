// web/src/fake-server.js -- the fake API. It plays the part of the Laravel API until week 12.
//
// It answers "requests" (a method, a path, a body) with "responses" (a status code and a
// body), using exactly the codes and shapes in the API design (PROJECT.pptx). Nothing outside
// api.js imports this file, so in week 12 it is deleted and nothing else notices.
//
// Where it comes from:
//   - MIDTERM_APPENDIX.md section A: an in-memory array, a 404 for a missing item, a 422
//     with an `errors` object. This file is that idea, for all five endpoints.
//   - inclass/wk3/pantry/src/Pantry.php: list() adds a status to every product; the rules
//     live in one place (rules.js) and every endpoint goes through them.
//   - The response shapes are Laravel's own (HW6-HW7, and HW9-10 next): paginate() gives
//     { data, links, meta }, a JsonResource wraps one item in { data }, and a failed
//     validation gives { message, errors }. Copying them now is what makes week 12 a
//     one-file change.

import {
  addDays, denied, dueStatus, daysLeft, todayIso, validateItem, LOCATIONS,
  EXPIRED, DUE_SOON, FRESH, NO_DATE,
} from './rules.js';

const PER_PAGE = 20;

// Fake people. Log in is not part of the midterm, so the prototype acts as one of them
// (Alex, unless the address says ?as=priya, ?as=ra or ?as=guest). In week 12 these move
// to api/database/seeders/DatabaseSeeder.php and a real log in decides who you are.
function seedUsers() {
  return [
    { id: 1, key: 'alex', name: 'Alex Rivera', email: 'alex@example.edu', role: 'user' },
    { id: 2, key: 'priya', name: 'Priya Shah', email: 'priya@example.edu', role: 'user' },
    { id: 3, key: 'ra', name: 'Jordan Lee', email: 'ra@example.edu', role: 'admin' },
  ];
}

// The starting shelf. Dates are counted from today, so "due soon" stays due soon whatever
// day the prototype is opened. Apples, Rice and Beans are HW4's three products.
function seedItems(today) {
  const stamp = (daysAgo) => `${addDays(today, -daysAgo)}T18:30:00.000000Z`;
  const row = (id, user_id, name, amount, unit, location, inDays, notes, addedDaysAgo) => ({
    id, user_id, name, amount, unit, location,
    best_before: inDays === null ? null : addDays(today, inDays),
    notes,
    created_at: stamp(addedDaysAgo),
    updated_at: stamp(Math.max(0, addedDaysAgo - 1)),
  });
  return [
    row(1, 1, 'Milk', 1, 'L', 'fridge', 3, '2%. Top shelf, door side.', 4),
    row(2, 2, 'Greek yogurt', 4, 'items', 'fridge', -2, null, 9),
    row(3, 2, 'Apples', 6, 'items', 'fridge', 10, 'Honeycrisp. Take one if you ask first.', 2),
    row(4, 1, 'Frozen dumplings', 24, 'items', 'freezer', 60, null, 12),
    row(5, 2, 'Rice', 2, 'kg', 'cabinet', 300, 'Jasmine.', 20),
    row(6, 1, 'Beans', 3, 'items', 'cabinet', 400, 'Cans of black beans.', 20),
    row(7, null, 'Leftover pasta', 1, 'items', 'fridge', -1, 'No name on the box.', 6),
    row(8, 3, 'Coffee', 340, 'g', 'cabinet', null, 'For the floor meeting. Help yourself.', 30),
  ];
}

/** The saved state, or null if there is none or it cannot be read. */
function readSaved(storage, key) {
  try {
    const text = storage?.getItem(key);
    return text ? JSON.parse(text) : null;
  } catch {
    return null;    // storage blocked, or a half-written value: start from the seed
  }
}

/**
 * A fresh fake API with its own copy of the data. `today` can be fixed, to try a
 * different day.
 *
 * `storage` (the app passes window.sessionStorage) keeps the data and who you are across a
 * page reload, the way a database and a session cookie will. sessionStorage belongs to one
 * browser tab, so a new tab starts again from the seed data.
 */
export function createFakeServer({ today = todayIso(), storage = null } = {}) {
  const KEY = 'floor-pantry-fake-api';
  const saved = readSaved(storage, KEY);
  let users = saved?.users ?? seedUsers();
  let items = saved?.items ?? seedItems(today);
  let nextItemId = saved?.nextItemId ?? items.length + 1;
  // Who is "logged in". A real session lives in a cookie the browser sends with every
  // request (Laravel, week 12); this variable is the fake's cookie. Alex by default.
  let sessionUserId = saved ? saved.sessionUserId : 1;

  function save() {
    storage?.setItem(KEY, JSON.stringify({ users, items, nextItemId, sessionUserId }));
  }

  const now = () => new Date().toISOString().replace('Z', '000Z');   // Laravel's format
  const me = () => users.find((u) => u.id === sessionUserId) ?? null;
  const publicUser = (u) => ({ id: u.id, name: u.name, email: u.email, role: u.role });

  // The item as the API sends it: the stored fields, plus what is worked out from them.
  // Like Pantry::list(), the status is computed on every read and never stored.
  function toResource(item) {
    const owner = users.find((u) => u.id === item.user_id);
    return {
      ...item,
      status: dueStatus(item.best_before, today),
      days_left: item.best_before ? daysLeft(item.best_before, today) : null,
      owner: owner ? { id: owner.id, name: owner.name } : null,
    };
  }

  // ------------------------------------------------------------ response helpers
  const ok = (body, status = 200) => ({ status, body });
  const noContent = () => ({ status: 204, body: null });
  const error = (status, message) => ({ status, body: { message } });
  const unauthenticated = () => error(401, 'Unauthenticated.');
  const invalid = (errors) => {
    // Laravel's message: the first problem, then "(and N more errors)".
    const all = Object.values(errors).flat();
    const more = all.length - 1;
    const message = more > 0 ? `${all[0]} (and ${more} more error${more > 1 ? 's' : ''})` : all[0];
    return { status: 422, body: { message, errors } };
  };
  const forbidden = (action) => error(403, action === 'delete'
    ? 'Only the RA can remove items from the shelf.'
    : 'Only the owner or the RA can change this item.');

  function findItem(id) {
    return items.find((i) => i.id === Number(id)) ?? null;
  }

  // ------------------------------------------------------------ the five endpoints

  // GET /api/items?location=fridge&status=due-soon&page=1
  function listItems(query) {
    const errors = {};
    const location = query.get('location');
    const status = query.get('status');
    const statuses = { expired: EXPIRED, 'due-soon': DUE_SOON, fresh: FRESH, 'no-date': NO_DATE };
    if (location && !LOCATIONS.includes(location)) errors.location = ['The selected location is invalid.'];
    if (status && !statuses[status]) errors.status = ['The selected status is invalid.'];
    if (Object.keys(errors).length) return invalid(errors);

    const page = Math.max(1, Number.parseInt(query.get('page') ?? '1', 10) || 1);
    const rows = items
      .map(toResource)
      .filter((i) => !location || i.location === location)
      .filter((i) => !status || i.status === statuses[status])
      // What goes off first comes first; undated items last; then by name.
      .sort((a, b) => (a.best_before ?? '9999').localeCompare(b.best_before ?? '9999')
        || a.name.localeCompare(b.name));

    const total = rows.length;
    const lastPage = Math.max(1, Math.ceil(total / PER_PAGE));
    return ok({
      data: rows.slice((page - 1) * PER_PAGE, page * PER_PAGE),
      meta: { current_page: page, last_page: lastPage, per_page: PER_PAGE, total },
    });
  }

  // GET /api/items/{id}
  function showItem(id) {
    const item = findItem(id);
    if (!item) return error(404, `No item with id ${id}.`);
    return ok({ data: toResource(item) });
  }

  // POST /api/items
  function createItem(body) {
    const user = me();
    const refused = denied(user, 'create');
    if (refused) return refused === 401 ? unauthenticated() : forbidden('create');

    const { values, errors } = validateItem(body, {
      sameNameExists: (name) => items.some((i) => i.user_id === user.id && i.name.toLowerCase() === name.toLowerCase()),
    });
    if (Object.keys(errors).length) return invalid(errors);

    // user_id comes from the session, never from the request body: you cannot add an
    // item in someone else's name by sending their id.
    const stamp = now();
    const item = { id: nextItemId++, user_id: user.id, ...values, created_at: stamp, updated_at: stamp };
    items = [...items, item];
    return ok({ data: toResource(item) }, 201);
  }

  // PUT /api/items/{id}
  // The order of the checks is Laravel's: logged in (401), then the item exists (404),
  // then you may change it (403), then the input is valid (422).
  function updateItem(id, body) {
    const user = me();
    if (!user) return unauthenticated();
    const item = findItem(id);
    if (!item) return error(404, `No item with id ${id}.`);
    if (denied(user, 'update', item)) return forbidden('update');

    const { values, errors } = validateItem(body, {
      partial: true,
      sameNameExists: (name) => items.some((i) => i.id !== item.id && i.user_id === item.user_id
        && i.name.toLowerCase() === name.toLowerCase()),
    });
    if (Object.keys(errors).length) return invalid(errors);

    const updated = { ...item, ...values, updated_at: now() };
    items = items.map((i) => (i.id === item.id ? updated : i));
    return ok({ data: toResource(updated) });
  }

  // DELETE /api/items/{id}
  function deleteItem(id) {
    const user = me();
    if (!user) return unauthenticated();
    const item = findItem(id);
    if (!item) return error(404, `No item with id ${id}.`);
    if (denied(user, 'delete', item)) return forbidden('delete');
    items = items.filter((i) => i.id !== item.id);
    return noContent();
  }

  // ------------------------------------------------------------ who you are

  // GET /api/user -- who is logged in. 401 when nobody is.
  function currentUser() {
    const user = me();
    return user ? ok({ data: publicUser(user) }) : unauthenticated();
  }

  /** Act as someone else: 'alex', 'priya', 'ra', or 'guest' (nobody). Stands in for log in. */
  function actAs(key) {
    sessionUserId = key === 'guest' ? null : users.find((u) => u.key === key)?.id ?? sessionUserId;
    save();
  }

  // ------------------------------------------------------------ the router

  /** Answer one request: handle('PUT', '/api/items/3', { amount: 2 }) -> { status, body } */
  function handle(method, url, body = {}) {
    const response = route(method, url, body);
    if (method !== 'GET') save();
    return response;
  }

  function route(method, url, body) {
    const { pathname, searchParams } = new URL(url, 'http://fake');
    const itemMatch = pathname.match(/^\/api\/items\/(\d+)$/);
    const route = `${method} ${itemMatch ? '/api/items/{id}' : pathname}`;

    switch (route) {
      case 'GET /api/items': return listItems(searchParams);
      case 'POST /api/items': return createItem(body ?? {});
      case 'GET /api/items/{id}': return showItem(itemMatch[1]);
      case 'PUT /api/items/{id}': return updateItem(itemMatch[1], body ?? {});
      case 'DELETE /api/items/{id}': return deleteItem(itemMatch[1]);
      case 'GET /api/user': return currentUser();
      default: return error(404, `No route for ${method} ${pathname}.`);
    }
  }

  return { handle, actAs };
}
