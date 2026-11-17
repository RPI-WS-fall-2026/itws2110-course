# The PRODUCTION image: your React build and your Laravel API in ONE container on port 8080.
# This file goes at the ROOT of your team repository, next to api/ and web/.
#
#   docker build -t my-project .        build it (from the repository root)
#
# Three stages. Only the last one ships; the first two exist to produce files for it.
# It is the Dockerfile from the platform's "Preparing your app" guide, without the two shared
# packages (hub login, platform bar) that you add when your project moves to the showcase.

# ---- 1. BUILD THE PAGES.  web/ -> web/dist: plain HTML, CSS and JavaScript files.
# Node is needed to build React. It is NOT needed to serve the result, so it stays here.
FROM node:22-bookworm-slim AS web
WORKDIR /src/web
# The two package files first: this slow layer is reused until one of them changes.
COPY web/package.json web/package-lock.json ./
RUN npm install --no-audit --no-fund
COPY web .
RUN npx vite build

# ---- 2. INSTALL THE API.  api/ with vendor/, and without the dev packages.
# --no-dev leaves out PHPUnit, Faker, Pail: tools for your laptop, not for the server.
FROM composer:2 AS api
WORKDIR /app
COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --ignore-platform-reqs
COPY api .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# ---- 3. WHAT RUNS.  FrankenPHP: one program that is a web server (Caddy) with PHP inside.
FROM dunglas/frankenphp:1-php8.4
# pdo_pgsql: production uses PostgreSQL. opcache: PHP keeps compiled code in memory.
RUN install-php-extensions pdo_pgsql pgsql opcache
# Production defaults. Everything else (APP_KEY, the database, APP_URL) arrives as
# environment variables when the container starts. There is no .env file in this image.
ENV SERVER_NAME=:8080 APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
WORKDIR /app
COPY --from=api /app /app
# The React build lands in Laravel's public/ folder: the server sends those files as they
# are, and hands every other path to Laravel (the fallback route in routes/web.php).
COPY --from=web /src/web/dist /app/public
RUN php artisan package:discover --ansi \
 && mkdir -p resources/views storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8080
# "Is it alive?" Laravel answers 200 at /up. The platform asks the same question.
HEALTHCHECK --interval=15s --timeout=3s --retries=5 CMD curl -fsS http://localhost:8080/up || exit 1
# Every start: bring the tables up to date and KEEP the data (migrate, never migrate:fresh),
# cache the settings, then serve.
CMD ["sh", "-c", "php artisan migrate --force --no-interaction && php artisan optimize --no-interaction && exec frankenphp php-server --root /app/public --listen :8080"]
