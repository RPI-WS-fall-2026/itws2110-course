// From HW4/HW5: React, Tailwind 4 as a Vite plugin, and the @ alias the shadcn
// components import from.
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } },
  // Other containers reach the dev server by its Compose service name, "web".
  // Vite refuses requests for host names it does not know, so we name it here.
  server: { allowedHosts: ['web'] },
});
