// Key page 2 and the form: who may do what, as each kind of person sees it.
import { test, expect } from '@playwright/test';
import { addItem, card, inDays, logIn, logOut, registerResident, unique } from './helpers.js';

test('a visitor who is not logged in can look, but not change anything', async ({ page }) => {
  await page.goto('/?location=fridge');
  await page.getByRole('link', { name: 'Milk', exact: true }).click();
  await expect(page.getByText('Log in to change your own items.')).toBeVisible();
  await expect(page.getByRole('link', { name: 'Edit' })).toHaveCount(0);
  await page.goto('/items/new');
  await expect(page.getByText('Log in to add what you put in the kitchen.')).toBeVisible();
});

test('a resident adds an item: the form shows what is wrong, then the item is on the shelf', async ({ page }) => {
  const me = await registerResident(page);
  await page.getByRole('link', { name: 'Add an item' }).first().click();
  await page.getByRole('button', { name: 'Add to shelf' }).click();
  await expect(page.getByText('The name is required.')).toBeVisible();
  await expect(page.getByText('The amount is required.')).toBeVisible();

  const name = unique('Ice cream');
  await page.getByLabel('Name').fill(name);
  await page.getByLabel('Amount').fill('1');
  await page.getByLabel('Unit').selectOption('L');
  await page.getByLabel('Where').selectOption('Freezer');
  await page.getByLabel('Best before').fill(inDays(200));
  await page.getByRole('button', { name: 'Add to shelf' }).click();

  await expect(page.getByRole('heading', { name })).toBeVisible();
  await expect(page.getByText('Best before in 200 days')).toBeVisible();
  await page.goto('/?location=freezer');
  await expect(card(page, name)).toContainText(me);
});

test('adding the same name twice is refused with the API\'s message', async ({ page }) => {
  await registerResident(page);
  const name = unique('Salsa');
  await addItem(page, { name });
  await page.goto('/items/new');
  await page.getByLabel('Name').fill(name);
  await page.getByLabel('Amount').fill('1');
  await page.getByRole('button', { name: 'Add to shelf' }).click();
  await expect(page.getByText(`You already have an item called ${name}. Change its amount instead.`)).toBeVisible();
});

test('the owner uses some: more than is on the shelf is refused, and the amount stays', async ({ page }) => {
  await registerResident(page);
  await addItem(page, { name: unique('Dumplings'), amount: '24', where: 'Freezer' });
  await page.getByLabel('Use some').fill('30');
  await page.getByRole('button', { name: 'Use', exact: true }).click();
  await expect(page.getByText('Not enough in stock')).toBeVisible();
  await expect(page.getByText('24 items')).toBeVisible();

  await page.getByLabel('Use some').fill('6');
  await page.getByRole('button', { name: 'Use', exact: true }).click();
  await expect(page.getByText('18 items')).toBeVisible();
  await expect(page.getByText('Not enough in stock')).toHaveCount(0);
});

test('the owner edits an item, and the change is saved', async ({ page }) => {
  await registerResident(page);
  const name = unique('Orange juice');
  const address = await addItem(page, { name, unit: 'L' });
  await page.getByRole('link', { name: 'Edit' }).click();
  await page.getByLabel('Amount').fill('-1');
  await page.getByRole('button', { name: 'Save changes' }).click();
  await expect(page.getByText('The amount cannot be below zero.')).toBeVisible();
  await page.getByLabel('Amount').fill('0.5');
  await page.getByRole('button', { name: 'Save changes' }).click();
  await expect(page.getByText('0.5 L')).toBeVisible();
  await page.goto(address);                                   // still there after a reload: it is in MySQL
  await expect(page.getByText('0.5 L')).toBeVisible();
});

test('a resident cannot change someone else\'s item, even by typing the address', async ({ page }) => {
  await registerResident(page);
  await page.goto('/?location=fridge');
  await page.getByRole('link', { name: 'Milk', exact: true }).click();      // Alex's
  await expect(page.getByText('Only Alex Rivera or the RA can change this item.')).toBeVisible();
  await page.goto(`${new URL(page.url()).pathname}/edit`);
  await expect(page.getByText('Only the owner or the RA can change Milk.')).toBeVisible();
});

test('only the RA sees Remove; removing takes the item off the shelf', async ({ page }) => {
  await registerResident(page);
  const name = unique('Old soup');
  const address = await addItem(page, { name, where: 'Cabinet', bestBefore: inDays(-3) });
  await expect(page.getByRole('button', { name: 'Remove from shelf' })).toHaveCount(0);   // the owner may not
  await logOut(page);

  await logIn(page, 'ra@example.edu');
  await page.goto(address);
  await page.getByRole('button', { name: 'Remove from shelf' }).click();
  await page.getByRole('button', { name: 'Yes, remove it' }).click();
  await expect(page.getByText(`Removed ${name} from the shelf.`)).toBeVisible();
  await page.goto(address);
  await expect(page.getByText(/There is no item/)).toBeVisible();
});
