#!/bin/sh
set -e

cd /var/www/html

# Regenerate the package manifest for the installed (no-dev) dependency set,
# in case the image copied in a stale one
php artisan package:discover --ansi || true

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
        if php -r '
            try {
                new PDO(
                    sprintf("%s:host=%s;port=%s;dbname=%s", getenv("DB_CONNECTION"), getenv("DB_HOST"), getenv("DB_PORT"), getenv("DB_DATABASE")),
                    getenv("DB_USERNAME"),
                    getenv("DB_PASSWORD"),
                    [PDO::ATTR_TIMEOUT => 3]
                );
                exit(0);
            } catch (Throwable $e) {
                fwrite(STDERR, "PDO attempt $try failed: ".$e->getMessage()."\n");
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
if [ "${db_ok:-0}" = "1" ]; then
    php artisan migrate --force || true
    php artisan db:seed --force || true
else
    echo "Skipping migrations: database unreachable."
fi

# Ensure no stale config cache can short-circuit env() feature flags —
# this app reads env() directly in routes/middleware, which returns null
# once config is cached (upstream LinkStack design).
php artisan config:clear 2>/dev/null || true

# Bind nginx to the platform-provided port (Render sets PORT; default 8080).
# Matches whichever numeric listen the build baked in (80/10000/8080).
LISTEN_PORT="${PORT:-8080}"
sed -i "s/^    listen [0-9]*;/    listen ${LISTEN_PORT};/" /etc/nginx/http.d/default.conf 2>/dev/null || true
echo "nginx listening on port ${LISTEN_PORT}"

echo "Entrypoint complete — starting services."
exec "$@"
