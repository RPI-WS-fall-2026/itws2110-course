// web/src/rules.js -- the rules of Floor Pantry, in one place, with no React and no data.
//
// Where these come from:
//   stock rules  -> inclass/wk3/pantry/src/Stock.php    (the "you cannot use more than you have" rule)
//   date rules   -> inclass/wk3/pantry/src/DueDate.php  (expired / due soon / fresh / no date)
//   field rules  -> inclass/wk3/pantry/src/Pantry.php   (create(): trim, required, unique, date format)
//   who may do what -> PROJECT_v3.pdf, Part 1, "Who may do what"
//
// Why a separate file: these are plain functions with no page and no server, so they are
// easy to test (week 3, Part 1). The fake API (fake-server.js) calls them to decide its
// answers, and the pages call `can()` to decide which buttons to show.
// In week 12 the same rules move to Laravel (a Form Request and a Policy).

// ---------------------------------------------------------------- the choices

export const UNITS = ['items', 'g', 'kg', 'ml', 'L'];
export const LOCATIONS = ['fridge', 'freezer', 'cabinet'];
export const NAME_MAX = 80;
export const NOTES_MAX = 500;
export const AMOUNT_MAX = 9999;

// ---------------------------------------------------------------- stock (Stock.php)

/** Thrown when a rule about amounts is broken. `field` says which form field to blame. */
export class RuleError extends Error {
  constructor(message, field = 'amount') {
    super(message);
    this.field = field;
  }
}

function assertPositive(quantity) {
  if (!(quantity > 0)) throw new RuleError('Amount must be greater than zero');
}

/** Buying some: the new amount. Stock::add() */
export function addStock(amount, quantity) {
  assertPositive(quantity);
  return amount + quantity;
}

/**
 * Using some: the new amount. Stock::consume() -- the rule that was missing in week 3.
 * Asking for more than is on the shelf is refused, and nothing changes.
 */
export function consumeStock(amount, quantity) {
  assertPositive(quantity);
  if (quantity > amount) throw new RuleError('Not enough in stock');
  return amount - quantity;
}

// ---------------------------------------------------------------- dates (DueDate.php)

export const EXPIRED = 'expired';
export const DUE_SOON = 'due soon';
export const FRESH = 'fresh';
export const NO_DATE = 'no date';

/** Today as "YYYY-MM-DD", in local time (toISOString would give UTC, a day off at night). */
export function todayIso(now = new Date()) {
  const pad = (n) => String(n).padStart(2, '0');
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

/** The ISO date `days` after `iso`. Used by the seed data so its dates never go stale. */
export function addDays(iso, days) {
  const [y, m, d] = iso.split('-').map(Number);
  return todayIso(new Date(y, m - 1, d + days));
}

/** Whole days from `today` until `bestBefore`. Negative once it has passed. */
export function daysLeft(bestBefore, today) {
  // Date.UTC on both sides, so a daylight-saving change can't make a day 23 hours long.
  const toDay = (iso) => {
    const [y, m, d] = iso.split('-').map(Number);
    return Date.UTC(y, m - 1, d) / 86_400_000;
  };
  return toDay(bestBefore) - toDay(today);
}

/**
 * One label for the shelf. Like DueDate::status(), "today" is an argument, not the clock,
 * which is what lets a test say what day it is.
 */
export function dueStatus(bestBefore, today, warnDays = 5) {
  if (bestBefore == null || bestBefore === '') return NO_DATE;
  const days = daysLeft(bestBefore, today);
  if (days < 0) return EXPIRED;
  if (days <= warnDays) return DUE_SOON;
  return FRESH;
}

// ---------------------------------------------------------------- fields (Pantry::create)

const ISO_DATE = /^\d{4}-\d{2}-\d{2}$/;

function isRealDate(iso) {
  if (!ISO_DATE.test(iso)) return false;
  const [y, m, d] = iso.split('-').map(Number);
  const date = new Date(y, m - 1, d);
  // new Date(2026, 1, 31) quietly becomes March 3rd; a real date survives the round trip.
  return date.getFullYear() === y && date.getMonth() === m - 1 && date.getDate() === d;
}

/**
 * Check an item's fields. Returns { values, errors }: `values` cleaned (trimmed, numbers
 * as numbers, '' as null) and `errors` in the shape of a Laravel 422 -- one array of
 * messages per field. An empty `errors` object means the input is fine.
 *
 * `partial: true` is for an update (PUT): a field that is left out is not checked,
 * because the update keeps its old value. A field that is sent is checked in full.
 *
 * `sameNameExists(name)` answers "does this owner already have an item called that?";
 * the caller supplies it because only the data store knows.
 */
export function validateItem(input, { partial = false, sameNameExists = () => false } = {}) {
  const errors = {};
  const values = {};
  const has = (field) => !partial || Object.hasOwn(input, field);
  const fail = (field, message) => { (errors[field] ??= []).push(message); };

  if (has('name')) {
    const name = String(input.name ?? '').trim();
    if (name === '') fail('name', 'The name is required.');
    else if (name.length > NAME_MAX) fail('name', `The name may not be longer than ${NAME_MAX} characters.`);
    else if (sameNameExists(name)) fail('name', `You already have an item called ${name}. Change its amount instead.`);
    values.name = name;
  }

  if (has('amount')) {
    const raw = input.amount;
    const amount = typeof raw === 'number' ? raw : Number(String(raw ?? '').trim());
    if (raw === '' || raw == null) fail('amount', 'The amount is required.');
    else if (!Number.isFinite(amount)) fail('amount', 'The amount must be a number.');
    else if (amount < 0) fail('amount', 'The amount cannot be below zero.');
    else if (amount > AMOUNT_MAX) fail('amount', `The amount may not be more than ${AMOUNT_MAX}.`);
    // Not `amount * 100 is a whole number`: 0.1 * 100 is 10.000000000000002 in floating point.
    else if (Math.abs(Math.round(amount * 100) - amount * 100) > 1e-9) fail('amount', 'The amount may have at most two decimal places.');
    values.amount = amount;
  }

  if (has('unit')) {
    if (!UNITS.includes(input.unit)) fail('unit', `The unit must be one of: ${UNITS.join(', ')}.`);
    values.unit = input.unit;
  }

  if (has('location')) {
    if (!LOCATIONS.includes(input.location)) fail('location', 'Choose where it is: fridge, freezer or cabinet.');
    values.location = input.location;
  }

  if (has('best_before')) {
    const date = String(input.best_before ?? '').trim();
    if (date !== '' && !isRealDate(date)) fail('best_before', 'The best-before date must be a date like 2026-10-30.');
    values.best_before = date === '' ? null : date;
  }

  if (has('notes')) {
    const notes = String(input.notes ?? '').trim();
    if (notes.length > NOTES_MAX) fail('notes', `The notes may not be longer than ${NOTES_MAX} characters.`);
    values.notes = notes === '' ? null : notes;
  }

  return { values, errors };
}

// ---------------------------------------------------------------- who may do what

/**
 * The table from PROJECT_v3.pdf Part 1, as a function. `user` is null when nobody is
 * logged in. `item` is needed for update and delete.
 *
 * It returns the HTTP status the API answers with when the action is refused (401 or
 * 403), or null when it is allowed. That way the fake API and the pages read one table:
 *   if (denied(user, 'update', item)) ...      in the fake API: answer with that status
 *   if (can(user, 'update', item)) ...         in a page: show the Edit button
 */
export function denied(user, action, item = null) {
  if (action === 'list' || action === 'show') return null;     // anyone
  if (!user) return 401;                                       // everything else needs a login
  if (user.role === 'admin') return null;                      // the RA may do anything
  if (action === 'create') return null;                        // any logged-in resident
  if (action === 'update') return item?.user_id === user.id ? null : 403;  // the owner
  return 403;                                                  // delete, and anything else
}

export const can = (user, action, item) => denied(user, action, item) === null;
