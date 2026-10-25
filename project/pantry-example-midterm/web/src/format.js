// web/src/format.js -- how values are shown. Kept out of the pages so every page says
// "1 L" and "in 3 days" the same way.

const LOCATION_NAMES = { fridge: 'Fridge', freezer: 'Freezer', cabinet: 'Cabinet' };

export const locationName = (location) => LOCATION_NAMES[location] ?? location;

/** 1 items -> "1 item", 2.5 kg -> "2.5 kg" */
export function formatAmount(amount, unit) {
  if (unit === 'items') return `${amount} ${amount === 1 ? 'item' : 'items'}`;
  return `${amount} ${unit}`;
}

/** "2026-10-28" -> "Wed, Oct 28". Parsed as a local date, not as UTC midnight. */
export function formatDate(iso) {
  const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
  return new Date(y, m - 1, d).toLocaleDateString('en-US', {
    weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
  });
}

/** days_left from the API -> words. */
export function formatDaysLeft(days) {
  if (days === null || days === undefined) return 'No best-before date';
  if (days === 0) return 'Best before today';
  if (days === 1) return 'Best before tomorrow';
  if (days > 1) return `Best before in ${days} days`;
  if (days === -1) return 'Went off yesterday';
  return `Went off ${-days} days ago`;
}

/** The owner line. An owner of null means the account was deleted (see PROJECT.pptx, "Our choices"). */
export const ownerName = (owner) => owner?.name ?? 'Former resident';
