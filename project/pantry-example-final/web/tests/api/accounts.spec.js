// Log in, register, log out, and the RA's list of users.
import { test, expect } from '@playwright/test';
import { client, newResident, theRA, uniqueEmail } from './client.js';

test('GET /api/user is 401 until someone logs in, and never includes the password', async () => {
  const api = await client();
  expect((await api.get('/api/user')).status()).toBe(401);
  const res = await api.post('/api/login', { email: 'alex@example.edu', password: 'password' });
  expect(res.status()).toBe(200);
  const me = (await (await api.get('/api/user')).json()).data;
  expect(me).toMatchObject({ name: 'Alex Rivera', email: 'alex@example.edu', role: 'user' });
  expect(me).not.toHaveProperty('password');
});

test('a wrong password and an unknown email get the same 422', async () => {
  const api = await client();
  const wrong = await api.post('/api/login', { email: 'alex@example.edu', password: 'nope' });
  const unknown = await api.post('/api/login', { email: uniqueEmail('nobody'), password: 'nope' });
  expect(wrong.status()).toBe(422);
  expect(await wrong.json()).toEqual(await unknown.json());
  expect((await wrong.json()).errors).toEqual({ email: ['These credentials do not match our records.'] });
});

test('registering logs you in as an ordinary resident, whatever role you send', async () => {
  const api = await client();
  const res = await api.post('/api/register', {
    name: 'Sam', email: uniqueEmail('sam'), password: 'a-long-password', password_confirmation: 'a-long-password', role: 'admin',
  });
  expect(res.status()).toBe(201);
  expect((await res.json()).data.role).toBe('user');
  expect((await (await api.get('/api/user')).json()).data.name).toBe('Sam');
});

test('registering checks every field', async () => {
  const api = await client();
  const res = await api.post('/api/register', { name: '', email: 'alex@example.edu', password: 'short', password_confirmation: 'short' });
  expect(res.status()).toBe(422);
  expect((await res.json()).errors).toEqual({
    name: ['The name is required.'],
    email: ['That email already has an account.'],
    password: ['The password must be at least 8 characters.'],
  });
});

test('logging out ends the session', async () => {
  const { api } = await newResident();
  expect((await api.post('/api/logout')).status()).toBe(204);
  expect((await api.get('/api/user')).status()).toBe(401);
});

test('GET /api/users: 401 logged out, 403 for a resident, 200 for the RA', async () => {
  expect((await (await client()).get('/api/users')).status()).toBe(401);
  const { api } = await newResident();
  expect((await api.get('/api/users')).status()).toBe(403);
  const ra = await theRA();
  const res = await ra.get('/api/users');
  expect(res.status()).toBe(200);
  const alex = (await res.json()).data.find((u) => u.email === 'alex@example.edu');
  expect(alex).toMatchObject({ name: 'Alex Rivera', role: 'user' });
  expect(alex.items_count).toBeGreaterThanOrEqual(1);
});

test('log in is rate-limited: the 21st try in a minute for one email gets 429', async () => {
  const api = await client();
  const email = uniqueEmail('guess');
  const statuses = [];
  for (let i = 0; i < 21; i++) {
    statuses.push((await api.post('/api/login', { email, password: `guess-${i}` })).status());
  }
  expect(statuses.slice(0, 20).every((s) => s === 422)).toBe(true);
  expect(statuses[20]).toBe(429);
});
