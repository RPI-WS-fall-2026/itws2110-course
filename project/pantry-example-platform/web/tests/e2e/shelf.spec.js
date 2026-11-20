// Key page 1, the shelf, as a person uses it -- now against the real API.
import { test, expect } from '@playwright/test';
import { card } from './helpers.js';

test('the shelf shows what is in the kitchen, with the status of each item', async ({ page }) => {
  await page.goto('/?location=fridge');
  await expect(page.getByRole('heading', { name: "What's in the kitchen" })).toBeVisible();
  await expect(card(page, 'Greek yogurt')).toContainText('expired');
  await expect(card(page, 'Milk')).toContainText('due soon');
  await expect(card(page, 'Milk')).toContainText('1 L · Fridge');
  await expect(card(page, 'Leftover pasta')).toContainText('Former resident');
  // What goes off first is at the top.
  await expect(page.getByRole('listitem').getByRole('link').first()).toHaveText('Greek yogurt');
});

test('the filters narrow the list, live in the address, and Back undoes them', async ({ page }) => {
  await page.goto('/');
  await page.getByRole('group', { name: 'Where' }).getByRole('button', { name: 'Fridge' }).click();
  await expect(page).toHaveURL(/location=fridge/);
  await page.getByRole('group', { name: 'Best before' }).getByRole('button', { name: 'Due soon' }).click();
  await expect(page).toHaveURL(/status=due-soon/);
  await expect(card(page, 'Milk')).toBeVisible();
  await expect(card(page, 'Greek yogurt')).toHaveCount(0);
  await page.goBack();
  await expect(card(page, 'Greek yogurt')).toBeVisible();
});

test('a filter with no matches says so, and offers the way back', async ({ page }) => {
  await page.goto('/?location=freezer&status=expired');
  await expect(page.getByText('Nothing matches these filters.')).toBeVisible();
  await page.getByRole('button', { name: 'Show everything' }).click();
  await expect(card(page, 'Milk')).toBeVisible();
});

test('open an item from the shelf, then go back', async ({ page }) => {
  await page.goto('/?location=fridge');
  await page.getByRole('link', { name: 'Milk', exact: true }).click();
  await expect(page).toHaveURL(/\/items\/\d+$/);
  await expect(page.getByRole('heading', { name: 'Milk' })).toBeVisible();
  await expect(page.getByText('Best before in 3 days')).toBeVisible();
  await expect(page.getByRole('article').getByText('Alex Rivera')).toBeVisible();
  await page.getByRole('link', { name: '← Back to the shelf' }).click();
  await expect(page.getByRole('heading', { name: "What's in the kitchen" })).toBeVisible();
});

test('an item that does not exist gets its own message (the API\'s 404)', async ({ page }) => {
  await page.goto('/items/999999');
  await expect(page.getByText('There is no item 999999.')).toBeVisible();
});

test('the loading state shows while the API answers', async ({ page }) => {
  // Hold the list request for a second, the way a slow network would.
  await page.route('**/api/items?*', async (route) => { await new Promise((r) => setTimeout(r, 1000)); await route.continue(); });
  await page.route('**/api/items', async (route) => { await new Promise((r) => setTimeout(r, 1000)); await route.continue(); });
  await page.goto('/');
  await expect(page.getByRole('status')).toHaveText('Loading the shelf…');
  await expect(card(page, 'Milk')).toBeVisible();
});

test('the error state shows when the API fails, and Try again recovers', async ({ page }) => {
  let fail = true;
  await page.route('**/api/items**', (route) => (fail ? route.fulfill({ status: 500, body: '{}' }) : route.continue()));
  await page.goto('/');
  await expect(page.getByRole('alert')).toContainText('Could not load the shelf');
  fail = false;
  await page.getByRole('button', { name: 'Try again' }).click();
  await expect(card(page, 'Milk')).toBeVisible();
});

test.describe('responsive (HW3): usable at 375 px and at 1280 px', () => {
  for (const [width, columns] of [[375, 1], [1280, 3]]) {
    test(`at ${width} px: ${columns} column(s), no sideways scrolling`, async ({ page }) => {
      await page.setViewportSize({ width, height: 800 });
      await page.goto('/?location=fridge');
      await expect(card(page, 'Milk')).toBeVisible();
      expect(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth)).toBe(false);
      const tops = await page.getByRole('listitem').evaluateAll((els) => els.slice(0, 3).map((el) => el.getBoundingClientRect().top));
      expect(tops.filter((t) => t === tops[0])).toHaveLength(columns);
    });
  }
});
