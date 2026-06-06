#!/bin/sh
set -e

APP_USER="${APP_USER:-theonefact}"
cd /var/www

# Persist the SQLite database in the mounted /data volume.
DB_PATH="${DB_DATABASE:-/data/database.sqlite}"
mkdir -p "$(dirname "$DB_PATH")"
[ -f "$DB_PATH" ] || touch "$DB_PATH"

# Ensure an environment file exists.
[ -f .env ] || cp .env.example .env

# The application processes run as $APP_USER (uid 1000), so it must own the
# data, the env file and the writable framework directories.
chown -R "$APP_USER":"$APP_USER" "$(dirname "$DB_PATH")"
chown "$APP_USER":"$APP_USER" .env
chown -R "$APP_USER":www-data storage bootstrap/cache

as_user() {
    runuser -u "$APP_USER" -- "$@"
}

# Generate an application key if one was neither supplied nor already stored.
if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env; then
    as_user php artisan key:generate --force --no-interaction
fi

# Ensure every process sees a concrete key. An empty APP_KEY in the container
# environment would otherwise shadow the value stored in .env.
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY=$(grep '^APP_KEY=' .env | head -n1 | cut -d= -f2-)
    export APP_KEY
fi

# Keep the schema up to date (safe to run on every boot).
as_user php artisan migrate --force --no-interaction

case "${1:-}" in
    ''|*supervisord)
        # Long-running mode: nginx + php-fpm + internal cron via supervisor.
        exec "$@"
        ;;
    php)
        exec runuser -u "$APP_USER" -- "$@"
        ;;
    *)
        # On-demand mode, e.g. `docker compose run --rm the-one-fact fact:generate`.
        exec runuser -u "$APP_USER" -- php artisan "$@"
        ;;
esac
