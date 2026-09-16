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

# Ensure APP_KEY is set (Render generateValue is random string, not base64)
if [ -z "$APP_KEY" ] || echo "$APP_KEY" | grep -q "CHANGE_ME"; then
  echo "APP_KEY missing, generating..."
  # Generate key and update .env + env
  GENERATED_KEY=$(php artisan key:generate --show 2>/dev/null || echo "base64:$(openssl rand -base64 32)")
  export APP_KEY="$GENERATED_KEY"
  # Update .env file for future boots
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
echo "Running migrations..."
# Retry DB connection up to 30s (Render DB may be slow to start)
for i in 1 2 3 4 5 6; do
  php artisan migrate --force 2>&1 && break
  echo "DB not ready, retry $i/6 in 5s..."
  sleep 5
done || echo "Migrations failed - check DB connection"

# Seed admin if needed (only if users table empty)
php artisan db:seed --class=AdminSeeder 2>&1 || echo "Seeding skipped"

# Show recent Laravel log on failure
if [ -f /var/www/html/storage/logs/laravel.log ]; then
  echo "=== Laravel log tail ==="
  tail -n 100 /var/www/html/storage/logs/laravel.log 2>/dev/null || true
fi

# Cache for performance (ignore failures) — use APP_DEBUG=true to see errors
if [ "$APP_DEBUG" = "false" ]; then
  php artisan config:cache 2>&1 || true
  php artisan route:cache 2>&1 || true
  php artisan view:cache 2>&1 || true
fi

# Test nginx config
nginx -t

echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
