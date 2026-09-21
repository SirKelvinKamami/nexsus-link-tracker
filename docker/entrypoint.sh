#!/bin/sh
set -e

cd /var/www/html

# Upstream components (geo-sot/laravel-env-editor, used by the finishing
# blade component) read AND write a physical .env file and fatal if it is
# missing - the case on Render, where config comes from real env vars and
# .env is gitignored. An empty file is a no-op for dotenv (existing
# environment variables are never overridden), but EnvEditor needs it.
touch .env

# PR preview instances copy every env var from the base service — including
# the PRODUCTION database connection. Booting a branch against prod would run
# its migrations on the live database. Make previews self-contained: a
# throwaway sqlite database, database drivers (their tables ship in the
# migration chain), and the preview's own URL instead of the prod APP_URL.
if [ "$IS_PULL_REQUEST" = "true" ] && [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "PR preview detected: switching to a throwaway sqlite database."
    export DB_CONNECTION=sqlite
    export DB_DATABASE="${DB_DATABASE:-/var/www/html/database/preview.sqlite}"
    unset DB_HOST DB_PORT DB_USERNAME DB_PASSWORD
    export SESSION_DRIVER=database CACHE_DRIVER=database QUEUE_CONNECTION=database
fi

# A preview instance must advertise its own onrender.com URL (absolute links,
# share URLs, password-reset links) rather than the production APP_URL it
# inherited.
if [ "$IS_PULL_REQUEST" = "true" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Generate APP_KEY on first boot if none was provided
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set — generating one..."
    # key:generate can only replace an existing APP_KEY= line, so seed one
    touch .env
    grep -q "^APP_KEY=" .env || echo "APP_KEY=" >> .env
    php artisan key:generate --force --no-interaction
fi

# Create the SQLite database file when using sqlite
if [ "$DB_CONNECTION" = "sqlite" ] && [ -n "$DB_DATABASE" ] && [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# Make sure storage and cache directories are writable by the fpm pool user
# (busybox /bin/sh has no brace expansion — list paths explicitly)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Ensure no stale config cache can short-circuit env() feature flags —
# this app reads env() directly in routes/middleware, which returns null
# once config is cached (upstream Nexsus Tracker design).
php artisan config:clear 2>/dev/null || true

# Bind nginx to the platform-provided port (Render sets PORT; default 8080).
# Matches whichever numeric listen the build baked in (80/10000/8080).
LISTEN_PORT="${PORT:-8080}"
# nginx.conf must contain EXACTLY ONE listen directive (enforced there);
# busybox sed has no GNU 0,/re/ address form, and a global rewrite is safe
# only while the one-listen invariant holds.
sed -i "s/    listen [0-9]*;/    listen ${LISTEN_PORT};/" /etc/nginx/http.d/default.conf 2>/dev/null || true
echo "nginx listening on port ${LISTEN_PORT}"

# Fire the slow, idempotent work (package:discover, migrations, seeding,
# admin audit, view-cache warm) in the BACKGROUND and start supervisord
# NOW: the web port opens while boot still runs. The health endpoint can
# answer before migrations finish, cutting free-tier cold-start time by the
# entire serial DB-wait + migrate + seed tail. The script is written to /tmp
# and run from there because the entrypoint never blocks, so an image update
# mid-boot can't rewrite the file out from under the running shell; output
# goes to stderr so Render's log stream still shows migration progress.
cp docker/boot-tasks.sh /tmp/boot-tasks.sh 2>/dev/null || true
chmod +x /tmp/boot-tasks.sh 2>/dev/null || true
/tmp/boot-tasks.sh 1>&2 2>&1 &

echo "Entrypoint complete — starting services (boot tasks running in background)."
exec "$@"
