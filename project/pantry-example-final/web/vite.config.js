// From HW4/HW5: React, Tailwind 4 as a Vite plugin, and the @ alias the shadcn
// components import from.
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } },
  server: {
    // The test containers reach the dev server by its Compose name, "web".
    allowedHosts: ['web'],
    // Forward the API's paths to Laravel. The browser only ever talks to this one address,
    // so the session cookie is a same-site cookie and there is no CORS to set up.
    // changeOrigin: false keeps the browser's own address in the Host header, so the
    // page links Laravel builds point back here, not at the container.
    proxy: {
      '/api': { target: process.env.API_URL ?? 'http://127.0.0.1:8000', changeOrigin: false },
      '/sanctum': { target: process.env.API_URL ?? 'http://127.0.0.1:8000', changeOrigin: false },
    },
  },
});
