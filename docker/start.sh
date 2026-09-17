#!/bin/sh
set -e

# Use Render's PORT if set, default to 10000 + keep 80
PORT=${PORT:-10000}
echo "Starting Nexsus Link Tracker on PORT=$PORT"

# Ensure nginx listens on Render's PORT (keep both 80 and PORT)
if [ "$PORT" != "80" ] && [ "$PORT" != "10000" ]; then
  # Replace 10000 with actual PORT if different
  sed -i "s/listen 10000;/listen $PORT;/g" /etc/nginx/http.d/default.conf || true
fi

# Ensure required directories exist
mkdir -p /var/log/supervisor /var/log/nginx /var/run
mkdir -p /var/www/html/storage/logs /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/bootstrap/cache
# Fix permissions — nginx (user nginx) needs read access, php-fpm (www) needs write
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
# Ensure web root is readable by nginx
chmod 755 /var/www/html 2>/dev/null || true
chmod 644 /var/www/html/index.php 2>/dev/null || true
# Remove maintenance mode file if present (can cause 403)
rm -f /var/www/html/storage/framework/maintenance.php 2>/dev/null || true
# Ensure .env exists — copy from example if missing
if [ ! -f /var/www/html/.env ]; then
  cp /var/www/html/.env.example /var/www/html/.env 2>/dev/null || true
fi

# Clean placeholder DB vars from .env.example copy (Render injects real values via env)
# If .env contains CHANGE_ME placeholders, remove those lines so env vars take precedence
if grep -q "CHANGE_ME" /var/www/html/.env 2>/dev/null; then
  echo "Removing placeholder DB vars from .env (using Render env vars)"
  sed -i "/CHANGE_ME/d" /var/www/html/.env || true
  sed -i "/^DB_HOST=/d; /^DB_PORT=/d; /^DB_DATABASE=/d; /^DB_USERNAME=/d; /^DB_PASSWORD=/d" /var/www/html/.env || true
  # Re-add DB_CONNECTION if missing
  if ! grep -q "^DB_CONNECTION=" /var/www/html/.env; then
    echo "DB_CONNECTION=pgsql" >> /var/www/html/.env
  fi
fi

# Detect placeholder DB_HOST from env (means Render DB not linked)
if echo "$DB_HOST" | grep -q "CHANGE_ME"; then
  echo "WARNING: DB_HOST is placeholder ($DB_HOST) — Render DB not linked!"
  echo "Falling back to SQLite for now (data will not persist across deploys)"
  export DB_CONNECTION=sqlite
  export DB_DATABASE=/var/www/html/database/database.sqlite
  mkdir -p /var/www/html/database
  touch /var/www/html/database/database.sqlite
  chown www:www /var/www/html/database/database.sqlite 2>/dev/null || true
  # Update .env fallback
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|" /var/www/html/.env 2>/dev/null || true
  echo "DB_DATABASE=/var/www/html/database/database.sqlite" >> /var/www/html/.env
fi

# Ensure APP_KEY is set (Render generateValue is random string, not base64)
if [ -z "$APP_KEY" ] || echo "$APP_KEY" | grep -q "CHANGE_ME"; then
  echo "APP_KEY missing, generating..."
  GENERATED_KEY=$(php artisan key:generate --show 2>/dev/null || echo "base64:$(openssl rand -base64 32)")
  export APP_KEY="$GENERATED_KEY"
  if grep -q "^APP_KEY=" /var/www/html/.env 2>/dev/null; then
    sed -i "s|^APP_KEY=.*|APP_KEY=$GENERATED_KEY|" /var/www/html/.env
  else
    echo "APP_KEY=$GENERATED_KEY" >> /var/www/html/.env
  fi
  echo "Generated APP_KEY"
fi

# Clear and cache config for production
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Wait for database to be ready and run migrations
echo "Running migrations on $DB_CONNECTION (host=${DB_HOST:-local})..."
for i in 1 2 3 4 5 6; do
  php artisan migrate --force 2>&1 && break
  echo "DB not ready, retry $i/6 in 5s..."
  sleep 5
done || echo "Migrations failed - check DB connection"

# Seed admin if needed (only if users table empty)
php artisan db:seed --class=AdminSeeder --force 2>&1 || echo "Seeding skipped"

# Show recent Laravel log on failure
if [ -f /var/www/html/storage/logs/laravel.log ]; then
  echo "=== Laravel log tail ==="
  tail -n 100 /var/www/html/storage/logs/laravel.log 2>/dev/null || true
fi

# NOTE: never config:cache this app — routes/middleware read env()
# directly, which returns null once config is cached (upstream design).
php artisan config:clear 2>&1 || true

# Test nginx config
nginx -t

echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
