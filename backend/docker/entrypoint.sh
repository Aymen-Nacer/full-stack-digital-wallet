#!/bin/bash
set -e

echo "==> Waiting for MySQL to be ready..."
MAX_RETRIES=30
RETRY=0
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ $RETRY -ge $MAX_RETRIES ]; then
        echo "ERROR: MySQL not reachable after $MAX_RETRIES retries. Exiting."
        exit 1
    fi
    echo "MySQL is not ready yet (attempt $RETRY/$MAX_RETRIES), retrying in 3 seconds..."
    sleep 3
done
echo "==> MySQL is ready!"

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache

echo "==> Starting Supervisor (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
