#!/usr/bin/env bash
set -e

# Ensure runtime ownership for writable Laravel dirs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Start PHP-FPM and Nginx
php-fpm -D
nginx -g "daemon off;"
