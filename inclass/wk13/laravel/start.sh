#!/bin/sh
# start.sh -- what the container does every time it starts (the Dockerfile's CMD runs this).
# On Azure a container starts on every deploy, and again whenever it wakes from zero.
#
# DRILL 3 is the first line below.
set -e

# Drop every table, build them again from the migrations, and put the seeder's rows in.
php artisan migrate --force

# Then serve. --host=0.0.0.0 so the server can be reached from outside the container.
# --no-reload hands the container's environment (docker-compose.yml) on to the app; without
# it, artisan's laptop server reads its settings from the .env file only.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
