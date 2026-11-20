// web/src/rules.js -- what the pages need to know about the rules.
//
// At the midterm this file held every rule, because there was no server. Now Laravel
// enforces them (app/Http/Requests, app/Policies, app/Support/Stock.php). What is left
// here is only what a page needs to draw itself: the choices for the form's menus, the
// status words, and a copy of "who may do what" to decide which buttons to show. If this
// copy is ever wrong, the API still refuses (403): hiding a button is a convenience.

export const UNITS = ['items', 'g', 'kg', 'ml', 'L'];
export const LOCATIONS = ['fridge', 'freezer', 'cabinet'];

// The status words the API sends (app/Support/DueDate.php).
export const EXPIRED = 'expired';
export const DUE_SOON = 'due soon';
export const FRESH = 'fresh';
export const NO_DATE = 'no date';

/** The table from PROJECT_v3.pdf Part 1: may this user do this? `user` is null when logged out. */
export function can(user, action, item = null) {
  if (action === 'list' || action === 'show') return true;
  if (!user) return false;
  if (user.role === 'admin') return true;
  if (action === 'create') return true;
  if (action === 'update') return item?.user_id === user.id;
  return false;
}
