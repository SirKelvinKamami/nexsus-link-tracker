#!/bin/sh
set -e

cd /var/www/html

# Regenerate the package manifest for the installed (no-dev) dependency set,
# in case the image copied in a stale one
php artisan package:discover --ansi || true

# Upstream components (geo-sot/laravel-env-editor, used by the finishing
# blade component) read AND write a physical .env file and fatal if it is
# missing - the case on Render, where config comes from real env vars and
# .env is gitignored. An empty file is a no-op for dotenv (existing
# environment variables are never overridden), but EnvEditor needs it.
touch .env

# Generate APP_KEY on first boot if none was provided
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set — generating one..."
    # key:generate can only replace an existing APP_KEY= line, so seed one
    touch .env
    grep -q "^APP_KEY=" .env || echo "APP_KEY=" >> .env
    php artisan key:generate --force --no-interaction
fi

# Wait for the database to accept connections (skip for sqlite)
# Wait (bounded) for the database — but NEVER block the web port from opening.
if [ "$DB_CONNECTION" != "sqlite" ] && [ -n "$DB_HOST" ]; then
    echo "Waiting for database at $DB_HOST:$DB_PORT (max 15 attempts)..."
    db_ok=0
    try=1
    while [ $try -le 15 ]; do
        if PDO_TRY="$try" php -r '
            try {
                new PDO(
                    sprintf("%s:host=%s;port=%s;dbname=%s", getenv("DB_CONNECTION"), getenv("DB_HOST"), getenv("DB_PORT"), getenv("DB_DATABASE")),
                    getenv("DB_USERNAME"),
                    getenv("DB_PASSWORD"),
                    [PDO::ATTR_TIMEOUT => 3]
                );
                exit(0);
            } catch (Throwable $e) {
                fwrite(STDERR, "PDO attempt ".getenv("PDO_TRY")." failed: ".$e->getMessage()."\n");
                exit(1);
            }
        '; then
            db_ok=1
            echo "Database is up (attempt $try)."
            break
        fi
        echo "  database not ready (attempt $try/15), retrying in 2s..."
        try=$((try + 1))
        sleep 2
    done
    if [ "$db_ok" != "1" ]; then
        echo "WARNING: database unreachable after 15 attempts - starting web server anyway."
    fi
else
    db_ok=1
fi

# Create the SQLite database file when using sqlite
if [ "$DB_CONNECTION" = "sqlite" ] && [ -n "$DB_DATABASE" ] && [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# Make sure storage and cache directories are writable by the fpm pool user
# (busybox /bin/sh has no brace expansion — list paths explicitly)
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache database 2>/dev/null || true

# Run pending migrations on every deploy (seeders are idempotent) —
# but only when the database answered; never block the web server on it.
#
# Failures are still non-fatal (the web port must open regardless), but they
# are now LOUD. Previously this was a bare `|| true`: when
# 2026_09_16_000006 died on Postgres the output scrolled past unremarked and
# every subsequent migration silently never ran, leaving production on a
# half-migrated schema for days. Any non-zero exit is now banner-logged and
# the remaining pending migrations are listed.
if [ "${db_ok:-0}" = "1" ]; then
    if php artisan migrate --force; then
        echo "Migrations applied cleanly."
    else
        echo "############################################################"
        echo "## MIGRATIONS FAILED - schema is INCOMPLETE."
        echo "## The app is starting anyway, but expect broken pages."
        echo "## Still pending:"
        php artisan migrate:status 2>/dev/null | grep -i "pending" || true
        echo "############################################################"
    fi

    if php artisan db:seed --force; then
        echo "Seeders applied cleanly."
    else
        echo "############################################################"
        echo "## SEEDERS FAILED - baseline data may be missing."
        echo "############################################################"
    fi
else
    echo "Skipping migrations: database unreachable."
fi

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

echo "Entrypoint complete — starting services."
exec "$@"
