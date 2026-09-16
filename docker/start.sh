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
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Clear and cache config for production
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# Wait for database to be ready and run migrations
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "Migrations failed or already done"

# Seed admin if needed (only if users table empty)
php artisan db:seed --class=AdminSeeder 2>&1 || echo "Seeding skipped"

# Cache for performance (ignore failures)
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

# Test nginx config
nginx -t

echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
