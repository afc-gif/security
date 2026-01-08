#!/bin/bash
set -e

cd /app/backend

mkdir -p storage/logs storage/framework/{cache,data,sessions,views} bootstrap/cache /run/nginx
chmod -R 775 storage bootstrap/cache /run/nginx
chown -R www-data:www-data storage bootstrap/cache /run/nginx public storage/logs

php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

# Start PHP-FPM in background
php-fpm -D

# Verify PHP-FPM is running
sleep 1
if ! pgrep -x php-fpm > /dev/null; then
  echo "ERROR: PHP-FPM failed to start"
  exit 1
fi

# Test nginx configuration
if ! nginx -t 2>&1; then
    exit 1
fi

# Start nginx in foreground
exec nginx -g 'daemon off;'
