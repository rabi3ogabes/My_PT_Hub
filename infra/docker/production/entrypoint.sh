#!/bin/sh
# Aurum app entrypoint: warm framework caches, optionally run migrations, then exec the role command.
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "[aurum] APP_KEY is not set — refusing to start." >&2
    exit 1
fi

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan event:cache --no-interaction

# Migrations run from exactly one place: the deploy job's one-off container
# (RUN_MIGRATIONS=true). --isolated takes a cache lock so parallel starts never race.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --isolated --no-interaction
fi

exec "$@"
