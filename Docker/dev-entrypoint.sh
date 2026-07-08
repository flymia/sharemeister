#!/bin/sh
# Startup script for the Sharemeister dev container.
# Prepares the app, runs migrations once the database is reachable, then serves.
set -e

cd /app

# First-run bootstrapping (safe to re-run on every start)
[ -f .env ] || { echo "[entrypoint] Creating .env from .env.example"; cp .env.example .env; }
[ -d vendor ] || { echo "[entrypoint] Installing composer dependencies"; composer install --no-interaction --prefer-dist; }
grep -q '^APP_KEY=base64:' .env || { echo "[entrypoint] Generating application key"; php artisan key:generate --force; }

# Run migrations. The db service is gated by a healthcheck, but retry anyway
# so the app comes up cleanly even if the DB needs a few more seconds.
echo "[entrypoint] Running database migrations"
n=0
until php artisan migrate --force; do
    n=$((n + 1))
    if [ "$n" -ge 10 ]; then
        echo "[entrypoint] Database not ready after $n attempts, aborting." >&2
        exit 1
    fi
    echo "[entrypoint] Migration attempt $n failed (DB not ready?), retrying in 3s..."
    sleep 3
done

# Expose storage/app/public via the public/ symlink (harmless if it exists)
php artisan storage:link 2>/dev/null || true

echo "[entrypoint] Starting Laravel dev server on http://0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
