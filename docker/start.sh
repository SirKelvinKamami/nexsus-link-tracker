#!/bin/sh
set -e

echo "Starting Nexsus Link Tracker..."

# Ensure required directories exist
mkdir -p /var/log/supervisor /var/log/nginx /var/run
mkdir -p /var/www/html/storage/logs /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache

# Fix permissions
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod 755 /var/www/html 2>/dev/null || true
chmod 644 /var/www/html/index.php 2>/dev/null || true

# Remove maintenance mode file
rm -f /var/www/html/storage/framework/maintenance.php 2>/dev/null || true

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
  cp /var/www/html/.env.example /var/www/html/.env 2>/dev/null || true
fi

# Clean placeholder vars from .env so env vars take precedence
sed -i "/CHANGE_ME/d" /var/www/html/.env 2>/dev/null || true

# ---- Database Connection ----
# Render may provide DATABASE_URL (full connection string) — use it if present
if [ -n "$DATABASE_URL" ]; then
  echo "Using DATABASE_URL for connection"
  export DB_CONNECTION=pgsql
  # Parse DATABASE_URL into parts if needed (Laravel 9+ supports DATABASE_URL natively)
  # But LinkStack may not use it, so also set individual vars
  export DB_HOST=$(echo "$DATABASE_URL" | sed -n 's|.*@\([^:]*\):\([0-9]*\)/.*|\1|p')
  export DB_PORT=$(echo "$DATABASE_URL" | sed -n 's|.*@\([^:]*\):\([0-9]*\)/.*|\2|p')
  export DB_DATABASE=$(echo "$DATABASE_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
  export DB_USERNAME=$(echo "$DATABASE_URL" | sed -n 's|://\([^:]*\):.*|\1|p')
  export DB_PASSWORD=$(echo "$DATABASE_URL" | sed -n 's|://[^:]*:\([^@]*\)@.*|\1|p')
  echo "DB: $DB_HOST:$DB_PORT/$DB_DATABASE (user=$DB_USERNAME)"
fi

# Fallback to SQLite if DB_HOST not set or still a placeholder
if [ -z "$DB_HOST" ] || echo "$DB_HOST" | grep -q "CHANGE_ME"; then
  echo "WARNING: No database configured — falling back to SQLite"
  export DB_CONNECTION=sqlite
  export DB_DATABASE=/var/www/html/database/database.sqlite
  mkdir -p /var/www/html/database
  touch /var/www/html/database/database.sqlite
  chown www:www /var/www/html/database/database.sqlite 2>/dev/null || true
fi

# ---- APP_KEY ----
if [ -z "$APP_KEY" ] || echo "$APP_KEY" | grep -q "CHANGE_ME"; then
  echo "Generating APP_KEY..."
  GENERATED_KEY="base64:$(openssl rand -base64 32)"
  export APP_KEY="$GENERATED_KEY"
  sed -i "s|^APP_KEY=.*|APP_KEY=$GENERATED_KEY|" /var/www/html/.env 2>/dev/null || echo "APP_KEY=$GENERATED_KEY" >> /var/www/html/.env
  echo "APP_KEY generated"
fi

# Clear cached configs before boot
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# ---- Migrations ----
echo "Running migrations (DB=$DB_CONNECTION)..."
for i in 1 2 3 4 5 6; do
  if php artisan migrate --force 2>&1; then
    echo "Migrations complete"
    break
  fi
  echo "DB not ready, retry $i/6 in 5s..."
  sleep 5
done

# ---- Seed ----
php artisan db:seed --class=AdminSeeder --force 2>/dev/null || true

# Show recent Laravel log on failure
if [ -f /var/www/html/storage/logs/laravel.log ]; then
  echo "=== Laravel log tail ==="
  tail -n 100 /var/www/html/storage/logs/laravel.log 2>/dev/null || true
fi

# NOTE: never config:cache this app — routes/middleware read env()
# directly, which returns null once config is cached (upstream design).
php artisan config:clear 2>&1 || true

# ---- Debug: dump config if APP_DEBUG ----
if [ "$APP_DEBUG" = "true" ]; then
  echo "=== DB Config ==="
  php artisan tinker --execute="echo config('database.default') . ' | ' . config('database.connections.pgsql.host', 'N/A');" 2>/dev/null || true
  echo ""
fi

# ---- Start ----
nginx -t
echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
