# One image for Homework 6: PHP, Composer, and this Laravel app with its
# dependencies already installed. `docker compose watch` runs it; the checks
# run inside it too.
#
# php:8.4-cli is the official PHP image without a web server. Laravel brings its own
# for development (`php artisan serve`), so there is no Apache here -- unlike HW1,
# where Apache was the point.
FROM php:8.4-cli

# Composer needs unzip to unpack packages quickly.
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip \
 && rm -rf /var/lib/apt/lists/*

# Composer is PHP's npm. Copy the binary straight out of its official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dependencies first, from the lockfile, so this slow layer is reused until
# composer.json or composer.lock change. vendor/ lives only inside the image.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader

# Then the app itself. The autoloader is built now that the classes are here, and
# .env is made from .env.example with a fresh APP_KEY -- the key that signs
# sessions and cookies. .env is never committed.
COPY . .
RUN composer dump-autoload --optimize \
 && cp -n .env.example .env \
 && php artisan key:generate --force --no-interaction \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

EXPOSE 8000

# Laravel's development server. --host=0.0.0.0 so it can be reached from outside
# the container; plain `php artisan serve` would only listen inside it.
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
