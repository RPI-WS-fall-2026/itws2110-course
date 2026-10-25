// The api suite: every endpoint, the success case and every error case (401, 403, 404,
// 422), as PROJECT_v3.pdf asks. These are the requests the midterm's fake server already
// answered; now Laravel answers them, with the same codes and the same bodies.
import { test, expect } from '@playwright/test';
import { anItem, client, newResident, theRA } from './client.js';

test.describe('GET /api/items', () => {
  test('200: a page of items with links and meta, soonest best-before first', async () => {
    const api = await client();
    const res = await api.get('/api/items');
    expect(res.status()).toBe(200);
    const body = await res.json();
    expect(body.meta).toMatchObject({ current_page: 1, per_page: 20 });
    expect(body.links).toHaveProperty('next');
    const dated = body.data.filter((i) => i.best_before).map((i) => i.best_before);
    expect(dated).toEqual([...dated].sort());
  });

  test('every item has the fields of the design, with status, days_left and owner', async () => {
    const api = await client();
    const milk = (await (await api.get('/api/items?location=fridge&status=due-soon')).json()).data.find((i) => i.name === 'Milk');
    expect(milk).toMatchObject({ unit: 'L', location: 'fridge', status: 'due soon', days_left: 3, owner: { name: 'Alex Rivera' } });
    expect(Object.keys(milk).sort()).toEqual(['amount', 'best_before', 'created_at', 'days_left', 'id',
      'location', 'name', 'notes', 'owner', 'status', 'unit', 'updated_at', 'user_id']);
  });

  test('filters by location and status', async () => {
    const api = await client();
    const body = await (await api.get('/api/items?location=freezer&status=fresh')).json();
    expect(body.data.length).toBeGreaterThan(0);
    for (const item of body.data) expect(item).toMatchObject({ location: 'freezer', status: 'fresh' });
  });

  test('422 for a filter value that does not exist', async () => {
    const api = await client();
    const res = await api.get('/api/items?location=sink');
    expect(res.status()).toBe(422);
    expect((await res.json()).errors).toEqual({ location: ['The selected location is invalid.'] });
  });
});

test.describe('GET /api/items/{id}', () => {
  test('200: the item, wrapped in data', async () => {
    const { api } = await newResident();
    const created = (await (await api.post('/api/items', anItem())).json()).data;
    const res = await (await client()).get(`/api/items/${created.id}`);   // anyone may look
    expect(res.status()).toBe(200);
    expect((await res.json()).data).toMatchObject({ id: created.id, name: created.name });
  });

  test('404 for an id that does not exist', async () => {
    const res = await (await client()).get('/api/items/999999');
    expect(res.status()).toBe(404);
    expect(await res.json()).toEqual({ message: 'No item with id 999999.' });
  });
});

test.describe('POST /api/items', () => {
  test('401 when nobody is logged in', async () => {
    const res = await (await client()).post('/api/items', anItem());
    expect(res.status()).toBe(401);
    expect(await res.json()).toEqual({ message: 'Unauthenticated.' });
  });

  test('201: the created item, owned by whoever is logged in; a user_id in the body is ignored', async () => {
    const { api, user } = await newResident();
    const res = await api.post('/api/items', { ...anItem({ amount: 1.5, unit: 'L' }), user_id: 1 });
    expect(res.status()).toBe(201);
    expect((await res.json()).data).toMatchObject({ amount: 1.5, unit: 'L', user_id: user.id, owner: { id: user.id } });
  });

  test('422 with a message per field, and Laravel\'s summary message', async () => {
    const { api } = await newResident();
    const res = await api.post('/api/items', { name: '', amount: '-2', unit: 'L', location: 'fridge' });
    expect(res.status()).toBe(422);
    expect(await res.json()).toEqual({
      message: 'The name is required. (and 1 more error)',
      errors: { name: ['The name is required.'], amount: ['The amount cannot be below zero.'] },
    });
  });

  test('422 when the owner already has an item with that name, in any case', async () => {
    const { api } = await newResident();
    await api.post('/api/items', anItem({ name: 'Hot sauce' }));
    const res = await api.post('/api/items', anItem({ name: 'hot sauce' }));
    expect(res.status()).toBe(422);
    expect((await res.json()).errors.name).toEqual(['You already have an item called hot sauce. Change its amount instead.']);
  });

  test('another resident may use the same name', async () => {
    const one = await newResident();
    const two = await newResident();
    expect((await one.api.post('/api/items', anItem({ name: 'Bread' }))).status()).toBe(201);
    expect((await two.api.post('/api/items', anItem({ name: 'Bread' }))).status()).toBe(201);
  });
});

test.describe('PUT /api/items/{id}', () => {
  test('401 when nobody is logged in', async () => {
    expect((await (await client()).put('/api/items/1', { amount: 0 })).status()).toBe(401);
  });

  test('404 for an id that does not exist', async () => {
    const { api } = await newResident();
    expect((await api.put('/api/items/999999', { amount: 0 })).status()).toBe(404);
  });

  test('403 for a resident changing someone else\'s item, and nothing changes', async () => {
    const owner = await newResident();
    const item = (await (await owner.api.post('/api/items', anItem())).json()).data;
    const other = await newResident();
    const res = await other.api.put(`/api/items/${item.id}`, { amount: 0 });
    expect(res.status()).toBe(403);
    expect(await res.json()).toEqual({ message: 'Only the owner or the RA can change this item.' });
    expect((await (await owner.api.get(`/api/items/${item.id}`)).json()).data.amount).toBe(2);
  });

  test('403 comes before 422: a stranger with bad input is told they may not, not what is wrong', async () => {
    const owner = await newResident();
    const item = (await (await owner.api.post('/api/items', anItem())).json()).data;
    const other = await newResident();
    expect((await other.api.put(`/api/items/${item.id}`, { amount: -1 })).status()).toBe(403);
  });

  test('200 for the owner: only the fields sent change', async () => {
    const { api } = await newResident();
    const item = (await (await api.post('/api/items', anItem({ unit: 'kg' }))).json()).data;
    const res = await api.put(`/api/items/${item.id}`, { amount: 0.5 });
    expect(res.status()).toBe(200);
    expect((await res.json()).data).toMatchObject({ name: item.name, amount: 0.5, unit: 'kg' });
  });

  test('200 for the RA, on anyone\'s item', async () => {
    const owner = await newResident();
    const item = (await (await owner.api.post('/api/items', anItem())).json()).data;
    const ra = await theRA();
    expect((await ra.put(`/api/items/${item.id}`, { notes: 'Bin on Friday.' })).status()).toBe(200);
  });

  test('422 for a negative amount', async () => {
    const { api } = await newResident();
    const item = (await (await api.post('/api/items', anItem())).json()).data;
    const res = await api.put(`/api/items/${item.id}`, { amount: -1 });
    expect(res.status()).toBe(422);
    expect((await res.json()).errors).toEqual({ amount: ['The amount cannot be below zero.'] });
  });
});

test.describe('DELETE /api/items/{id}', () => {
  test('401 when nobody is logged in', async () => {
    expect((await (await client()).delete('/api/items/1')).status()).toBe(401);
  });

  test('403 even for the owner: removing is the RA\'s job', async () => {
    const { api } = await newResident();
    const item = (await (await api.post('/api/items', anItem())).json()).data;
    const res = await api.delete(`/api/items/${item.id}`);
    expect(res.status()).toBe(403);
    expect(await res.json()).toEqual({ message: 'Only the RA can remove items from the shelf.' });
  });

  test('204 for the RA, then the item is gone (404)', async () => {
    const { api } = await newResident();
    const item = (await (await api.post('/api/items', anItem())).json()).data;
    const ra = await theRA();
    const res = await ra.delete(`/api/items/${item.id}`);
    expect(res.status()).toBe(204);
    expect((await ra.get(`/api/items/${item.id}`)).status()).toBe(404);
    expect((await ra.delete(`/api/items/${item.id}`)).status()).toBe(404);
  });
});

test.describe('POST /api/items/{id}/use (new since the midterm)', () => {
  test('using some takes it off the shelf; more than is there is refused, and nothing changes', async () => {
    const { api } = await newResident();
    const item = (await (await api.post('/api/items', anItem({ amount: 24 }))).json()).data;

    const tooMuch = await api.post(`/api/items/${item.id}/use`, { quantity: 30 });
    expect(tooMuch.status()).toBe(422);
    expect((await tooMuch.json()).errors).toEqual({ quantity: ['Not enough in stock'] });

    const ok = await api.post(`/api/items/${item.id}/use`, { quantity: 6 });
    expect(ok.status()).toBe(200);
    expect((await ok.json()).data.amount).toBe(18);
  });

  test('403 for someone else\'s item; 422 for zero', async () => {
    const owner = await newResident();
    const item = (await (await owner.api.post('/api/items', anItem())).json()).data;
    const other = await newResident();
    expect((await other.api.post(`/api/items/${item.id}/use`, { quantity: 1 })).status()).toBe(403);
    expect((await owner.api.post(`/api/items/${item.id}/use`, { quantity: 0 })).status()).toBe(422);
  });
});
