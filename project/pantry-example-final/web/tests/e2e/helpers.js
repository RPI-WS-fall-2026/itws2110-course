// Shared steps for the browser tests. Each test registers its own resident and makes its
// own items ("own data per test", PROJECT_v3.pdf), so tests never depend on each other or
// on what the database already holds.
import { expect } from '@playwright/test';

let counter = 0;
export const unique = (word) => `${word} ${Date.now() % 1_000_000}-${counter++}`;

/** Register a new resident through the page. Ends logged in, on the shelf. */
export async function registerResident(page, name = unique('Resident')) {
  await page.goto('/register');
  await page.getByLabel('Name').fill(name);
  await page.getByLabel('Email').fill(`${name.replace(/\W+/g, '-').toLowerCase()}-${process.pid}@example.edu`);
  await page.getByLabel('Password', { exact: true }).fill('a-long-password');
  await page.getByLabel('Confirm password').fill('a-long-password');
  await page.getByRole('button', { name: 'Create account' }).click();
  await expect(page.getByTestId('signed-in-as')).toContainText(name);
  return name;
}

/** Log in as a seeded person. */
export async function logIn(page, email) {
  await page.goto('/login');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password').fill('password');
  await page.getByRole('button', { name: 'Log in' }).click();
  await expect(page.getByTestId('signed-in-as')).toBeVisible();
}

export async function logOut(page) {
  await page.getByRole('button', { name: 'Log out' }).click();
  await expect(page.getByRole('banner').getByRole('link', { name: 'Log in' })).toBeVisible();
}

/** Add an item through the form. Ends on the item's page; returns its address. */
export async function addItem(page, { name, amount = '1', unit = 'items', where = 'Fridge', bestBefore = '' }) {
  await page.goto('/items/new');
  await page.getByLabel('Name').fill(name);
  await page.getByLabel('Amount').fill(amount);
  await page.getByLabel('Unit').selectOption(unit);
  await page.getByLabel('Where').selectOption(where);
  if (bestBefore) await page.getByLabel('Best before').fill(bestBefore);
  await page.getByRole('button', { name: 'Add to shelf' }).click();
  await expect(page.getByRole('heading', { name })).toBeVisible();
  return new URL(page.url()).pathname;
}

/** A date `days` from today, as the date field wants it. */
export function inDays(days) {
  const d = new Date();
  d.setDate(d.getDate() + days);
  return d.toISOString().slice(0, 10);
}

/** The card for one item on the shelf, found by its link. */
export const card = (page, name) => page.getByRole('listitem').filter({ has: page.getByRole('link', { name, exact: true }) });
