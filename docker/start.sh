#!/bin/bash
set -e

cd /app/backend

mkdir -p storage/logs storage/framework/{cache,data,sessions,views} bootstrap/cache /run/nginx
chmod -R 775 storage bootstrap/cache /run/nginx
chown -R www-data:www-data storage bootstrap/cache /run/nginx public storage/logs

# Run database migrations (production only)
if [ "$APP_ENV" = "production" ]; then
  php artisan migrate --force 2>&1 || true
fi

php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

php-fpm -D

if ! nginx -t 2>&1; then
    exit 1
fi

exec nginx -g 'daemon off;'
