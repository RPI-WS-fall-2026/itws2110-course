# One image for the week 9 workshop: PHP, Composer, and this Laravel app with its
# dependencies already installed. `docker compose watch` runs it next to a MySQL
# container; the drill checks run inside it too, on an in-memory SQLite database.
FROM php:8.4-cli

# Composer needs unzip to unpack packages quickly. pdo_mysql is the PHP extension that
# speaks to MySQL -- the same one HW1's Dockerfile installed. (SQLite support is built in.)
RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install pdo_mysql

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

# Start fresh every time the container starts: drop the tables, run every migration,
# seed, then serve. The pantry's data is the seeder's, so nothing is lost by this.
# --host=0.0.0.0 so the server can be reached from outside the container.
CMD ["sh", "-c", "php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=8000"]
