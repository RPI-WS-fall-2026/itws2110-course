// Two suites, one config (week 3 Parts 3 and 4; HW2):
//   api  -- Playwright's `request`: every endpoint, no browser       tests/api
//   e2e  -- Playwright in Chromium: the pages as a person uses them   tests/e2e
// Both go through the web container (http://web:5173), the same way a browser does, so
// the session cookie and the CSRF token are tested too.
import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.BASE_URL ?? 'http://localhost:5181';

export default defineConfig({
  timeout: 20_000,
  expect: { timeout: 5_000 },
  reporter: [['list']],
  use: { baseURL },
  projects: [
    { name: 'api', testDir: './tests/api' },
    { name: 'e2e', testDir: './tests/e2e', use: { ...devices['Desktop Chrome'] } },
  ],
});
