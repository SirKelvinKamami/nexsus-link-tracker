#!/bin/sh
# Background boot tasks: work that users don't need to see on the very first
# page load. The entrypoint launches this detached and IMMEDIATELY starts
# supervisord (web port opens), so slow idempotent work no longer serializes
# in front of nginx — the main win for Render free-tier cold starts.
set -e
cd /var/www/html

# Regenerate the package manifest for the installed (no-dev) dependency set,
# in case the image copied in a stale one. Pure I/O when current.
php artisan package:discover --ansi >/dev/null 2>&1 || true

# Wait (bounded) for the database — boot-tasks runs after the web port is
# already open, so waiting here costs nothing user-visible.
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
        echo "WARNING: database unreachable after 15 attempts - skipping migrations."
        exit 0
    fi
else
    # Create the SQLite database file when using sqlite
    if [ -n "$DB_DATABASE" ] && [ ! -f "$DB_DATABASE" ]; then
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
    fi
fi

# Run pending migrations on every deploy (seeders are idempotent).
#
# Failures are non-fatal (the web server is already up), but they are LOUD.
# Previously a bare `|| true` buried a failing migration and production ran
# half-migrated for days. Any non-zero exit is banner-logged and the pending
# list is printed.
if php artisan migrate --force; then
    echo "Migrations applied cleanly."
else
    echo "############################################################"
    echo "## MIGRATIONS FAILED - schema is INCOMPLETE."
    echo "## The app is up, but expect broken pages."
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

# Warn if any account still holds a known-default password. Deployments
# seeded before AdminSeeder was fixed still have admin@admin.com / 12345678:
# the seeder skips accounts that already exist, so a weak hash survives every
# later deploy and no amount of redeploying clears it. Advisory only.
php artisan nexsus:rotate-admin-password --audit >/dev/null 2>&1 || true

# Warm the view cache (Blade -> PHP compilation): the first request after a
# cold start otherwise compiles dozens of blades serially. Then make sure the
# runtime-writable asset/database paths exist — the favicon icons directory
# is written at runtime and 500s the whole site if missing (that failure
# class hit CI four times).
if php artisan view:cache >/dev/null 2>&1; then
    echo "View cache warmed."
else
    php artisan view:clear >/dev/null 2>&1 || true
fi
mkdir -p assets/favicon/icons
chown -R www-data:www-data assets/favicon/icons storage bootstrap/cache database 2>/dev/null || true

echo "background boot tasks complete."
