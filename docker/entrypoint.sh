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
if [ "$DB_CONNECTION" != "sqlite" ] && [ -n "$DB_HOST" ]; then
    echo "Waiting for database at $DB_HOST:$DB_PORT..."
    until php -r '
        try {
            new PDO(
                sprintf("%s:host=%s;port=%s;dbname=%s", getenv("DB_CONNECTION"), getenv("DB_HOST"), getenv("DB_PORT"), getenv("DB_DATABASE")),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD"),
                [PDO::ATTR_TIMEOUT => 3]
            );
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        sleep 2
    done
    echo "Database is up."
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

# Run pending migrations on every deploy (seeders are idempotent)
php artisan migrate --force || true
php artisan db:seed --force || true

# Cache config for production speed — but never fail the boot on it
# (safe: no route/view caching here)
php artisan config:cache || php artisan config:clear

# Bind nginx to the platform-provided port (Render sets PORT); default 8080
LISTEN_PORT="${PORT:-8080}"
sed -i "s/^    listen 80;/    listen ${LISTEN_PORT};/" /etc/nginx/http.d/default.conf 2>/dev/null || true
echo "nginx listening on port ${LISTEN_PORT}"

echo "Entrypoint complete — starting services."
exec "$@"
