#!/bin/bash
set -e

cd /app/backend

mkdir -p storage/logs storage/framework/{cache,data,sessions,views} bootstrap/cache /run/nginx
chmod -R 775 storage bootstrap/cache /run/nginx
chown -R www-data:www-data storage bootstrap/cache /run/nginx public storage/logs

# Run migrations automatically
echo "Running database migrations..."
if ! php artisan migrate --force 2>&1; then
    echo "ERROR: Database migrations failed. Refusing to start with an out-of-date schema."
    exit 1
fi

php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true

# Start PHP-FPM in background
php-fpm -D

# Give PHP-FPM a moment to start
sleep 2

# Render Nginx config with the runtime port
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
echo "INFO: Runtime PORT is ${PORT}"
echo "INFO: Nginx listen lines:"
nginx -T 2>/dev/null | grep -E "listen" || true

# Test nginx configuration
if ! nginx -t 2>&1; then
    echo "ERROR: Nginx configuration test failed"
    exit 1
fi

# Start nginx in foreground
exec nginx -g 'daemon off;'
