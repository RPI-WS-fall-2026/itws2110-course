// Log in, register, log out, and the admin page -- against Laravel and Sanctum.
import { test, expect } from '@playwright/test';
import { logIn, logOut, registerResident } from './helpers.js';

test('a wrong password is refused with one message for the email field', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Email').fill('alex@example.edu');
  await page.getByLabel('Password').fill('wrong');
  await page.getByRole('button', { name: 'Log in' }).click();
  await expect(page.getByText('These credentials do not match our records.')).toBeVisible();
});

test('log in shows the name; it lasts through a reload; log out ends it', async ({ page }) => {
  await logIn(page, 'alex@example.edu');
  await expect(page.getByTestId('signed-in-as')).toHaveText('Alex Rivera');
  await page.reload();                                         // the session cookie survives
  await expect(page.getByTestId('signed-in-as')).toHaveText('Alex Rivera');
  await expect(page.getByRole('banner').getByRole('link', { name: 'Admin' })).toHaveCount(0);
  await logOut(page);
  await page.reload();
  await expect(page.getByTestId('signed-in-as')).toHaveCount(0);
});

test('register shows what is wrong, then logs the new resident in', async ({ page }) => {
  await page.goto('/register');
  await page.getByLabel('Name').fill('Sam');
  await page.getByLabel('Email').fill('alex@example.edu');
  await page.getByLabel('Password', { exact: true }).fill('short');
  await page.getByLabel('Confirm password').fill('short');
  await page.getByRole('button', { name: 'Create account' }).click();
  await expect(page.getByText('That email already has an account.')).toBeVisible();
  await expect(page.getByText('The password must be at least 8 characters.')).toBeVisible();
  await registerResident(page);
});

test('the admin page: the RA sees residents and the clean-out list; a resident does not', async ({ page }) => {
  await registerResident(page);
  await page.goto('/admin');
  await expect(page.getByText('Only the RA can see this page.')).toBeVisible();
  await logOut(page);

  await logIn(page, 'ra@example.edu');
  await page.getByRole('banner').getByRole('link', { name: 'Admin' }).click();
  const cleanOut = page.getByRole('region', { name: 'Clean-out list' });
  await expect(cleanOut.getByRole('link', { name: 'Greek yogurt' })).toBeVisible();
  await expect(cleanOut.getByRole('link', { name: 'Leftover pasta' })).toBeVisible();
  const residents = page.getByRole('region', { name: 'Residents' });
  await expect(residents.getByRole('row', { name: /Jordan Lee/ })).toContainText('RA (admin)');
});
