// A small client for the API tests, doing what a browser does: get the CSRF cookie,
// send it back as a header, keep the session cookie. (Week 3 Part 3 and HW2 used
// Playwright's `request` the same way, without the log in.)
import { request as playwrightRequest, expect } from '@playwright/test';

const baseURL = process.env.BASE_URL ?? 'http://localhost:5181';
let counter = 0;

/** A unique email, so every test has its own user and its own data. */
export const uniqueEmail = (tag = 'test') =>
  `${tag}-${Date.now()}-${process.pid}-${counter++}@example.edu`;

export async function client() {
  const ctx = await playwrightRequest.newContext({
    baseURL,
    // Sanctum treats a request as coming from the app when its Referer is the app's address.
    extraHTTPHeaders: { Accept: 'application/json', Referer: `${baseURL}/` },
  });
  await ctx.get('/sanctum/csrf-cookie');

  async function send(method, url, data) {
    const { cookies } = await ctx.storageState();
    const token = cookies.find((c) => c.name === 'XSRF-TOKEN')?.value;
    return ctx.fetch(url, { method, data, headers: token ? { 'X-XSRF-TOKEN': decodeURIComponent(token) } : {} });
  }

  return {
    get: (url) => ctx.get(url),
    post: (url, data) => send('POST', url, data),
    put: (url, data) => send('PUT', url, data),
    delete: (url) => send('DELETE', url),
    dispose: () => ctx.dispose(),
  };
}

/** A logged-in client for a brand-new resident. Returns the client and the user. */
export async function newResident() {
  const api = await client();
  const res = await api.post('/api/register', {
    name: 'Test Resident', email: uniqueEmail('resident'), password: 'a-long-password', password_confirmation: 'a-long-password',
  });
  expect(res.status()).toBe(201);
  return { api, user: (await res.json()).data };
}

/** A logged-in client for the seeded RA. */
export async function theRA() {
  const api = await client();
  const res = await api.post('/api/login', { email: 'ra@example.edu', password: 'password' });
  expect(res.status()).toBe(200);
  return api;
}

/** A valid item, with a name no other test uses. */
export const anItem = (overrides = {}) => ({
  name: `Test item ${Date.now()}-${counter++}`, amount: 2, unit: 'items', location: 'fridge', best_before: null, notes: null, ...overrides,
});
