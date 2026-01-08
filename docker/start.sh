#!/bin/bash
set -e

cd /app/backend

mkdir -p storage/logs storage/framework/{cache,data,sessions,views} bootstrap/cache /run/nginx /run/php-fpm
chmod -R 775 storage bootstrap/cache /run/nginx /run/php-fpm
chown -R www-data:www-data storage bootstrap/cache /run/nginx /run/php-fpm public storage/logs

php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

# Start PHP-FPM in background
php-fpm -D

# Give PHP-FPM a moment to start and create socket
sleep 2

# Verify socket exists
if [ ! -S /run/php-fpm.sock ]; then
  echo "ERROR: PHP-FPM socket not created at /run/php-fpm.sock"
  sleep 5
fi

# Test nginx configuration
if ! nginx -t 2>&1; then
    echo "ERROR: Nginx configuration test failed"
    exit 1
fi

# Start nginx in foreground
exec nginx -g 'daemon off;'
