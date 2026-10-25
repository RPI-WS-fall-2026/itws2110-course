# The Laravel API: PHP, Composer, and this app with its dependencies installed.
# The same Dockerfile as HW7 and the week 9 workshop. `docker compose watch` runs it next
# to MySQL; the unit suite runs inside it too.
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
# seed, then serve. Fine on a laptop: the data is the seeder's. A deployed copy must NOT
# do this -- it runs `php artisan migrate --force` instead, which keeps the data.
# If a migration or the seeder fails, say so and serve anyway, so the error is on screen.
#
# Served with PHP's own web server (php -S) rather than `php artisan serve`: artisan serve
# starts a child process that reads only api/.env, so the database password that
# docker-compose.yml passes in would be ignored. php -S keeps the container's settings.
# Four workers, so the browser and the tests can make requests at the same time.
ENV PHP_CLI_SERVER_WORKERS=4
CMD ["sh", "-c", "php artisan migrate:fresh --seed --force || echo '*** The migrations or the seeder failed: the error is above. Fix it, save, then run: docker compose exec app php artisan migrate:fresh --seed'; cd public && exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]
