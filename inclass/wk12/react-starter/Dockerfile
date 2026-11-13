# One image for the React starter kit: PHP (Laravel) and Node (Vite) together.
# Inertia apps need both while you develop: Laravel serves every page, Vite serves the React
# code with hot reload, and the Wayfinder plugin inside Vite runs `php artisan` to generate
# the typed route helpers in resources/js. So one container runs both processes.
FROM php:8.4-cli

# unzip for Composer; the extensions the starter kit's packages need (intl for number and
# date formatting; bcmath for Fortify's two-factor codes). SQLite support is built in.
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip libicu-dev \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install intl bcmath

# Composer and Node, copied from their official images.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
 && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /app

# Dependencies first, from the lockfiles, so these slow layers are reused until a lockfile
# changes. vendor/ and node_modules/ live only inside the image (built for Linux, not macOS).
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

# Then the app. .env comes from .env.example with a fresh APP_KEY; the SQLite database is a
# file inside the container.
COPY . .
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi \
 && cp -n .env.example .env \
 && php artisan key:generate --force --no-interaction \
 && touch database/database.sqlite \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

EXPOSE 8000 5174

# Migrate and seed (keeps the data you registered; the seeder only adds the sample logins if
# they are missing), then run Laravel and Vite side by side. Ctrl-C in
# the watch terminal, or `docker compose down`, stops both.
CMD ["sh", "-c", "php artisan migrate --force --seed && npx concurrently -k -n laravel,vite -c blue,magenta \"php artisan serve --host=0.0.0.0 --port=8000\" \"npm run dev\""]
